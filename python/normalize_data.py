#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Preprocesamiento de lecturas meteorológicas.
1. Lectura flexible (Excel / CSV utf-16 tabulada / CSV estándar).
2. Centinelas de sensor (--, --.-, ---) a NaN.
3. Imputación: interpolación temporal limitada (~1 h), mediana en rachas largas
   y viento, moda en dirección del viento.
4. Cálculo de punto de rocío y sensación térmica si siguen faltando.
5. Detección de outliers (IQR, Z-score, Isolation Forest, DBSCAN) SIN eliminar
   observaciones: se etiquetan. El consenso de 4 métodos marca outlier_consenso.
6. Exporta Excel para descarga y CSV alineado a la tabla lecturas.
"""

import json
import math
import os
import sys
import unicodedata
from datetime import datetime
from pathlib import Path

os.environ.setdefault("LOKY_MAX_CPU_COUNT", "1")
os.environ.setdefault("JOBLIB_MULTIPROCESSING", "0")

import numpy as np
import pandas as pd
from openpyxl import Workbook
from openpyxl.styles import Alignment, Border, Font, PatternFill, Side
from openpyxl.utils.dataframe import dataframe_to_rows

CENTINELAS_NUM = ["--", "--.-"]
CENTINELA_DIR = ["---"]
UMBRAL_INTERPOLACION = 12  # ~1 h si el intervalo es 5 min

COLUMNAS_DB = [
    "fecha_lectura",
    "intervalo",
    "temp_interna",
    "humedad_interna",
    "temp_externa",
    "humedad_externa",
    "presion_relativa",
    "presion_absoluta",
    "viento_vel",
    "viento_rafaga",
    "viento_dir",
    "punto_rocio",
    "sensacion_termica",
    "lluvia_hora",
    "lluvia_dia",
    "lluvia_semana",
    "lluvia_mes",
    "lluvia_total",
]

COLS_INTERP = ["temp_externa", "humedad_externa", "punto_rocio", "sensacion_termica"]
COLS_MEDIANA = ["viento_vel", "viento_rafaga"]
COLS_NUMERICAS_OUTLIERS = [
    "temp_interna",
    "humedad_interna",
    "temp_externa",
    "humedad_externa",
    "presion_relativa",
    "presion_absoluta",
    "viento_vel",
    "viento_rafaga",
    "punto_rocio",
    "sensacion_termica",
    "lluvia_hora",
    "lluvia_dia",
    "lluvia_semana",
    "lluvia_mes",
    "lluvia_total",
]

HEADERS_ES = {
    "fecha_lectura": "Fecha/Hora",
    "intervalo": "Intervalo",
    "temp_interna": "Temperatura Interna(°C)",
    "humedad_interna": "Humedad Interna(%)",
    "temp_externa": "Temperatura Externa(°C)",
    "humedad_externa": "Humedad Externa(%)",
    "presion_relativa": "Presión Relativa(mmHg)",
    "presion_absoluta": "Presión Absoluta(mmHg)",
    "viento_vel": "Velocidad del viento(m/s)",
    "viento_rafaga": "Ráfaga(m/s)",
    "viento_dir": "Dirección del viento",
    "punto_rocio": "Punto de Rocío(°C)",
    "sensacion_termica": "Sensación Térmica(°C)",
    "lluvia_hora": "Lluvia hora(mm)",
    "lluvia_dia": "Lluvia 24 horas(mm)",
    "lluvia_semana": "Lluvia semana(mm)",
    "lluvia_mes": "Lluvia mes(mm)",
    "lluvia_total": "Lluvia Total(mm)",
    "outlier_iqr": "outlier_iqr",
    "outlier_zscore": "outlier_zscore",
    "outlier_isolation_forest": "outlier_isolation_forest",
    "cluster_dbscan": "cluster_dbscan",
    "outlier_consenso": "outlier_consenso",
}


def native(value):
    if value is None or (isinstance(value, float) and (math.isnan(value) or math.isinf(value))):
        return None
    if hasattr(value, "item"):
        value = value.item()
    if isinstance(value, (np.bool_,)):
        return bool(value)
    if pd.isna(value):
        return None
    if isinstance(value, pd.Timestamp):
        return value.isoformat()
    return value


def normalizar_nombre_columna(nombre):
    nombre = str(nombre).strip().lower()
    nombre = unicodedata.normalize("NFKD", nombre)
    nombre = "".join([c for c in nombre if not unicodedata.combining(c)])
    nombre = nombre.replace("%", " ").replace("(", " ").replace(")", " ").replace(".", " ").replace(":", " ")
    nombre = nombre.replace("°", " ").replace("°c", " ").replace("mmhg", " ")
    nombre = nombre.replace("/", " ").replace("-", " ").replace("_", " ").replace("\n", " ").replace("\r", " ")
    nombre = " ".join(nombre.split())
    return nombre


def mapear_columna(nombre):
    original = str(nombre).strip()
    aliases = {
        "Fecha/Hora": "fecha_lectura",
        "Fecha/Hora_dt": "fecha_lectura",
        "Intervalo": "intervalo",
        "No.": "numero",
        "N°": "numero",
        "Temperatura Interna(°C)": "temp_interna",
        "Humedad Interna(%)": "humedad_interna",
        "Temperatura Externa(°C)": "temp_externa",
        "Humedad Externa(%)": "humedad_externa",
        "Presión Relativa(mmHg)": "presion_relativa",
        "Presión Absoluta(mmHg)": "presion_absoluta",
        "Velocidad del viento(m/s)": "viento_vel",
        "Ráfaga(m/s)": "viento_rafaga",
        "Dirección del viento": "viento_dir",
        "Punto de Rocío(°C)": "punto_rocio",
        "Sensación Térmica(°C)": "sensacion_termica",
        "Lluvia hora(mm)": "lluvia_hora",
        "Lluvia 24 horas(mm)": "lluvia_dia",
        "Lluvia semana(mm)": "lluvia_semana",
        "Lluvia mes(mm)": "lluvia_mes",
        "Lluvia Total(mm)": "lluvia_total",
    }
    if original in aliases:
        return aliases[original]

    nombre = normalizar_nombre_columna(nombre)
    if "fecha" in nombre and "hora" in nombre:
        return "fecha_lectura"
    if "timestamp" in nombre:
        return "fecha_lectura"
    if "intervalo" in nombre:
        return "intervalo"
    if ("temp" in nombre or "temperatura" in nombre) and ("interna" in nombre or "inte" in nombre):
        return "temp_interna"
    if ("temp" in nombre or "temperatura" in nombre) and ("externa" in nombre or "exterior" in nombre or "ext" in nombre):
        return "temp_externa"
    if "humedad" in nombre and ("interna" in nombre or "inte" in nombre):
        return "humedad_interna"
    if "humedad" in nombre and ("externa" in nombre or "exterior" in nombre):
        return "humedad_externa"
    if "presion" in nombre and ("relati" in nombre):
        return "presion_relativa"
    if "presion" in nombre and ("absolu" in nombre):
        return "presion_absoluta"
    if "rafaga" in nombre:
        return "viento_rafaga"
    if "viento" in nombre and "dir" in nombre:
        return "viento_dir"
    if "direccion" in nombre:
        return "viento_dir"
    if "velocidad" in nombre or ("vel" in nombre and "viento" in nombre):
        return "viento_vel"
    if "punto" in nombre and "roci" in nombre:
        return "punto_rocio"
    if "sensacion" in nombre:
        return "sensacion_termica"
    if "lluvia" in nombre and "24" in nombre:
        return "lluvia_dia"
    if "lluvia" in nombre and "hora" in nombre:
        return "lluvia_hora"
    if "lluvia" in nombre and "semana" in nombre:
        return "lluvia_semana"
    if "lluvia" in nombre and "mes" in nombre:
        return "lluvia_mes"
    if "lluvia" in nombre and "total" in nombre:
        return "lluvia_total"
    if nombre in ["n", "no", "numero", "n"]:
        return "numero"
    return None


def calcular_punto_rocio(temperatura, humedad):
    if pd.isna(temperatura) or pd.isna(humedad) or humedad <= 0:
        return np.nan
    a = 17.27
    b = 237.7
    alpha = ((a * temperatura) / (b + temperatura)) + np.log(humedad / 100.0)
    return round((b * alpha) / (a - alpha), 1)


def calcular_sensacion_termica(temperatura, velocidad_viento, humedad):
    if pd.isna(temperatura) or pd.isna(velocidad_viento):
        return np.nan
    if temperatura < 10:
        wc = 13.12 + (0.6215 * temperatura) - (11.37 * (velocidad_viento ** 0.16)) + (
            0.3965 * temperatura * (velocidad_viento ** 0.16)
        )
        return round(wc, 1)
    if temperatura > 26 and not pd.isna(humedad):
        t = temperatura
        rh = humedad
        hi = (
            -42.379
            + 2.04901523 * t
            + 10.14333127 * rh
            - 0.22475541 * t * rh
            - 0.00683783 * t**2
            - 0.05481717 * rh**2
            + 0.00122874 * t**2 * rh
            + 0.00085282 * t * rh**2
            - 0.00000199 * t**2 * rh**2
        )
        return round(hi, 1)
    return round(float(temperatura), 1)


def leer_archivo(input_file):
    path = Path(input_file)
    suffix = path.suffix.lower()
    if suffix in {".xlsx", ".xls"}:
        return pd.read_excel(input_file)

    encodings = ["utf-16", "utf-16-le", "utf-8-sig", "utf-8", "latin-1"]
    delimiters = ["\t", ";", ","]
    last_error = None
    for encoding in encodings:
        for delimiter in delimiters:
            try:
                df = pd.read_csv(input_file, encoding=encoding, delimiter=delimiter)
                if df.shape[1] >= 4:
                    return df
            except Exception as exc:
                last_error = exc
    raise ValueError(f"No se pudo leer el archivo: {last_error}")


def parsear_fecha(serie):
    s = serie.astype(str).str.strip().str.replace("\u00a0", " ", regex=False)
    s = s.str.replace("a. m.", "AM", regex=False).str.replace("p. m.", "PM", regex=False)
    s = s.str.replace("a.m.", "AM", regex=False).str.replace("p.m.", "PM", regex=False)
    dt = pd.to_datetime(s, format="%d/%m/%Y %I:%M:%S %p", errors="coerce")
    if dt.isna().mean() > 0.5:
        dt = pd.to_datetime(serie, errors="coerce", dayfirst=True)
    return dt


def imputar(df):
    columnas_presentes = [col for col in COLS_INTERP if col in df.columns]
    nulos_iniciales = int(df[columnas_presentes].isna().any(axis=1).sum()) if columnas_presentes else 0

    for col in COLS_INTERP:
        if col in df.columns:
            df[col] = df[col].interpolate(method="time", limit=UMBRAL_INTERPOLACION, limit_direction="both")
            df[col] = df[col].round(1)
            if df[col].isna().any():
                df[col] = df[col].fillna(df[col].median())

    for col in COLS_MEDIANA:
        if col in df.columns:
            df[col] = df[col].fillna(df[col].median())

    if "viento_dir" in df.columns:
        moda = df["viento_dir"].mode()
        if len(moda) > 0:
            df["viento_dir"] = df["viento_dir"].fillna(moda.iloc[0])

    if "temp_externa" in df.columns and "humedad_externa" in df.columns:
        mask = df["punto_rocio"].isna() if "punto_rocio" in df.columns else pd.Series(True, index=df.index)
        if "punto_rocio" not in df.columns:
            df["punto_rocio"] = np.nan
        df.loc[mask, "punto_rocio"] = [
            calcular_punto_rocio(t, h)
            for t, h in zip(df.loc[mask, "temp_externa"], df.loc[mask, "humedad_externa"])
        ]

    if "temp_externa" in df.columns and "viento_vel" in df.columns:
        mask = df["sensacion_termica"].isna() if "sensacion_termica" in df.columns else pd.Series(True, index=df.index)
        if "sensacion_termica" not in df.columns:
            df["sensacion_termica"] = np.nan
        hum = df["humedad_externa"] if "humedad_externa" in df.columns else pd.Series(np.nan, index=df.index)
        df.loc[mask, "sensacion_termica"] = [
            calcular_sensacion_termica(t, v, h)
            for t, v, h in zip(df.loc[mask, "temp_externa"], df.loc[mask, "viento_vel"], hum.loc[mask])
        ]

    return df, nulos_iniciales


def detectar_outliers(df):
    cols = [c for c in COLS_NUMERICAS_OUTLIERS if c in df.columns]
    tabla_iqr = pd.DataFrame(index=df.index)
    filas_iqr = []
    for col in cols:
        datos = pd.to_numeric(df[col], errors="coerce")
        q1, q3 = datos.quantile(0.25), datos.quantile(0.75)
        iqr = q3 - q1
        lo, hi = q1 - 1.5 * iqr, q3 + 1.5 * iqr
        mask = (datos < lo) | (datos > hi)
        tabla_iqr[col] = mask.fillna(False)
        filas_iqr.append({
            "variable": col,
            "q1": native(round(q1, 2) if pd.notna(q1) else None),
            "q3": native(round(q3, 2) if pd.notna(q3) else None),
            "limite_inferior": native(round(lo, 2) if pd.notna(lo) else None),
            "limite_superior": native(round(hi, 2) if pd.notna(hi) else None),
            "cantidad": int(mask.fillna(False).sum()),
            "porcentaje": native(round(mask.fillna(False).mean() * 100, 2)),
        })
    mask_iqr = tabla_iqr.any(axis=1) if not tabla_iqr.empty else pd.Series(False, index=df.index)

    tabla_z = pd.DataFrame(index=df.index)
    filas_z = []
    for col in cols:
        datos = pd.to_numeric(df[col], errors="coerce")
        std = datos.std()
        if pd.isna(std) or std == 0:
            mask = pd.Series(False, index=df.index)
        else:
            z = (datos - datos.mean()) / std
            mask = z.abs() > 3
        tabla_z[col] = mask.fillna(False)
        filas_z.append({
            "variable": col,
            "cantidad": int(mask.fillna(False).sum()),
            "porcentaje": native(round(mask.fillna(False).mean() * 100, 2)),
        })
    mask_z = tabla_z.any(axis=1) if not tabla_z.empty else pd.Series(False, index=df.index)

    mask_iso = pd.Series(False, index=df.index)
    mask_db = pd.Series(False, index=df.index)
    labels_iso = pd.Series(1, index=df.index, dtype=int)
    labels_db = pd.Series(0, index=df.index, dtype=int)
    sklearn_ok = False
    sklearn_error = None
    n_clusters = 0

    try:
        from sklearn.cluster import DBSCAN
        from sklearn.ensemble import IsolationForest
        from sklearn.preprocessing import StandardScaler

        X = df[cols].apply(pd.to_numeric, errors="coerce").fillna(0)
        X_scaled = StandardScaler().fit_transform(X)
        iso = IsolationForest(n_estimators=200, contamination="auto", random_state=42, n_jobs=1)
        pred_iso = iso.fit_predict(X_scaled)
        labels_iso = pd.Series(pred_iso, index=df.index)
        mask_iso = labels_iso == -1

        dbscan = DBSCAN(eps=1.5, min_samples=10)
        pred_db = dbscan.fit_predict(X_scaled)
        labels_db = pd.Series(pred_db, index=df.index)
        mask_db = labels_db == -1
        n_clusters = len(set(pred_db)) - (1 if -1 in pred_db else 0)
        sklearn_ok = True
    except Exception as exc:
        sklearn_ok = False
        sklearn_error = f"{type(exc).__name__}: {exc}"

    consenso = mask_iqr & mask_z & mask_iso & mask_db if sklearn_ok else mask_iqr & mask_z

    df = df.copy()
    df["outlier_iqr"] = mask_iqr.astype(int)
    df["outlier_zscore"] = mask_z.astype(int)
    df["outlier_isolation_forest"] = (labels_iso == -1).astype(int)
    df["cluster_dbscan"] = labels_db.astype(int)
    df["outlier_consenso"] = consenso.astype(int)

    n = len(df)
    comparacion = [
        {"metodo": "IQR", "cantidad": int(mask_iqr.sum()), "porcentaje": round(float(mask_iqr.mean() * 100), 2) if n else 0},
        {"metodo": "Z-score", "cantidad": int(mask_z.sum()), "porcentaje": round(float(mask_z.mean() * 100), 2) if n else 0},
        {"metodo": "Isolation Forest", "cantidad": int(mask_iso.sum()), "porcentaje": round(float(mask_iso.mean() * 100), 2) if n else 0},
        {"metodo": "DBSCAN", "cantidad": int(mask_db.sum()), "porcentaje": round(float(mask_db.mean() * 100), 2) if n else 0},
    ]

    return df, {
        "sklearn_disponible": sklearn_ok,
        "sklearn_error": sklearn_error,
        "clusters_dbscan": n_clusters,
        "comparacion": comparacion,
        "detalle_iqr": filas_iqr,
        "detalle_zscore": filas_z,
        "coincidencias": {
            "iqr_zscore": int((mask_iqr & mask_z).sum()),
            "iqr_isolation": int((mask_iqr & mask_iso).sum()),
            "zscore_isolation": int((mask_z & mask_iso).sum()),
            "isolation_dbscan": int((mask_iso & mask_db).sum()),
            "cuatro_metodos": int(consenso.sum()),
        },
        "decision": (
            "No se eliminan outliers: viento y lluvia extremos se conservan como eventos reales. "
            "Solo se etiquetan. outlier_consenso marca coincidencia de los métodos disponibles."
        ),
    }


def calcular_pronostico_horario(df, horas=12):
    """Estima las siguientes horas usando el histórico disponible en el archivo."""
    columnas = ["fecha_lectura", "temp_externa"]
    historico = df.copy()
    if any(col not in historico.columns for col in columnas):
        return {"available": False, "message": "Faltan fecha_lectura o temp_externa.", "data_points": 0, "items": [], "source": "uploaded_file"}

    historico = historico.dropna(subset=columnas).sort_values("fecha_lectura")
    if len(historico) < 6:
        return {"available": False, "message": "Se necesitan al menos 6 lecturas con fecha y temperatura.", "data_points": int(len(historico)), "items": [], "source": "uploaded_file"}

    reciente = historico.tail(6)
    base = pd.Timestamp(historico.iloc[-1]["fecha_lectura"]).floor("h")
    items = []

    def promedio(filas, columna):
        if columna not in filas.columns:
            return None
        valores = pd.to_numeric(filas[columna], errors="coerce").dropna()
        return round(float(valores.mean()), 1) if not valores.empty else None

    for offset in range(1, horas + 1):
        objetivo = base + pd.Timedelta(hours=offset)
        misma_hora = historico[historico["fecha_lectura"].dt.hour == objetivo.hour]
        muestra = misma_hora if not misma_hora.empty else reciente
        peso_historico = 0.7 if not misma_hora.empty else 0.35

        def combinar(columna):
            historico_promedio = promedio(muestra, columna)
            reciente_promedio = promedio(reciente, columna)
            if historico_promedio is None:
                return reciente_promedio
            if reciente_promedio is None:
                return historico_promedio
            return round((historico_promedio * peso_historico) + (reciente_promedio * (1 - peso_historico)), 1)

        temperatura = combinar("temp_externa")
        lluvia = combinar("lluvia_dia") if "lluvia_dia" in historico.columns else combinar("lluvia_hora")
        riesgo = "Riesgo de helada" if temperatura is not None and temperatura <= 2 else "Lluvia intensa" if lluvia is not None and lluvia >= 10 else "Condición estable"
        items.append({
            "datetime": objetivo.isoformat(),
            "label": objetivo.strftime("%H:%M"),
            "date_label": objetivo.strftime("%d/%m"),
            "temperature": temperatura,
            "humidity": combinar("humedad_externa"),
            "wind": combinar("viento_vel"),
            "rain": lluvia,
            "risk": riesgo,
            "confidence": "Media" if len(misma_hora) >= 3 else "Baja",
        })

    return {
        "available": True,
        "message": "Estimación estadística basada en las lecturas del archivo cargado.",
        "generated_at": datetime.now().astimezone().isoformat(),
        "timezone": "America/Bogota",
        "data_points": int(len(historico)),
        "items": items,
        "source": "uploaded_file",
    }


def escribir_excel(df, output_file, summary, outliers):
    wb = Workbook()
    ws = wb.active
    ws.title = "Datos preprocesados"
    header_fill = PatternFill(start_color="4472C4", end_color="4472C4", fill_type="solid")
    header_font = Font(bold=True, color="FFFFFF")
    border = Border(
        left=Side(style="thin"),
        right=Side(style="thin"),
        top=Side(style="thin"),
        bottom=Side(style="thin"),
    )

    export_cols = [c for c in list(HEADERS_ES.keys()) if c in df.columns]
    visible = df[export_cols].rename(columns=HEADERS_ES)

    for col_idx, col_name in enumerate(visible.columns, start=1):
        cell = ws.cell(row=1, column=col_idx, value=col_name)
        cell.fill = header_fill
        cell.font = header_font
        cell.alignment = Alignment(horizontal="center", vertical="center")
        cell.border = border

    for row_idx, row in enumerate(dataframe_to_rows(visible, index=False, header=False), start=2):
        for col_idx, value in enumerate(row, start=1):
            cell = ws.cell(row=row_idx, column=col_idx, value=None if pd.isna(value) else value)
            cell.border = border

    for col in ws.columns:
        max_length = 0
        letter = col[0].column_letter
        for cell in col:
            if cell.value is not None:
                max_length = max(max_length, len(str(cell.value)))
        ws.column_dimensions[letter].width = min(max_length + 2, 42)

    ws_summary = wb.create_sheet("Resumen")
    ws_summary["A1"] = "RESUMEN DE PREPROCESAMIENTO"
    ws_summary["A1"].font = Font(bold=True, size=12)
    rows = [
        ["Total de registros", summary["total_registros"]],
        ["Registros imputados (sensor externo)", summary["registros_imputados"]],
        ["Fecha de inicio", summary["fecha_inicio"]],
        ["Fecha de fin", summary["fecha_fin"]],
        ["Temperatura promedio (°C)", summary["temp_promedio"]],
        ["Temperatura mínima (°C)", summary["temp_minima"]],
        ["Temperatura máxima (°C)", summary["temp_maxima"]],
        ["Humedad promedio (%)", summary["humedad_promedio"]],
        ["Lluvia 24h máxima (mm)", summary["lluvia_total"]],
        ["Viento promedio (m/s)", summary["vel_viento_promedio"]],
        ["Outliers consenso", summary["outliers_consenso"]],
        ["Decisión", outliers["decision"]],
    ]
    for i, (k, v) in enumerate(rows, start=3):
        ws_summary.cell(row=i, column=1, value=k).font = Font(bold=True)
        ws_summary.cell(row=i, column=2, value=v)

    ws_out = wb.create_sheet("Outliers")
    ws_out["A1"] = "Comparación de métodos"
    ws_out["A1"].font = Font(bold=True, size=12)
    ws_out.append(["Método", "Cantidad", "Porcentaje"])
    for item in outliers["comparacion"]:
        ws_out.append([item["metodo"], item["cantidad"], item["porcentaje"]])

    ws_summary.column_dimensions["A"].width = 42
    ws_summary.column_dimensions["B"].width = 28
    wb.save(output_file)


def preprocesar(input_file, output_file):
    df = leer_archivo(input_file)
    df = df.copy()
    columnas_map = {}
    for col in df.columns:
        mapped = mapear_columna(col)
        if mapped:
            columnas_map[col] = mapped
    df = df.rename(columns=columnas_map)
    if "numero" in df.columns:
        df = df.drop(columns=["numero"])

    existentes = [c for c in ["fecha_lectura", "temp_externa", "humedad_externa", "viento_vel"] if c in df.columns]
    if len(existentes) < 3:
        raise ValueError(
            "El archivo no contiene suficientes columnas esperadas "
            "(Fecha/Hora, Temperatura Externa, Humedad Externa, Velocidad del viento)."
        )

    if "fecha_lectura" in df.columns:
        df["fecha_lectura"] = parsear_fecha(df["fecha_lectura"])

    if "viento_dir" in df.columns:
        df["viento_dir"] = df["viento_dir"].astype(str).str.strip().replace(CENTINELA_DIR + ["nan", "None"], pd.NA)

    numericas = [c for c in COLUMNAS_DB if c not in ("fecha_lectura", "viento_dir")]
    for col in numericas:
        if col in df.columns:
            serie = df[col].astype(str).str.strip().replace(CENTINELAS_NUM, pd.NA)
            df[col] = pd.to_numeric(serie, errors="coerce")

    df = df.dropna(subset=["fecha_lectura"])
    df = df.sort_values("fecha_lectura").drop_duplicates(subset=["fecha_lectura"], keep="last")
    df = df.set_index("fecha_lectura")

    df, nulos_iniciales = imputar(df)
    df = df.reset_index()
    df, outliers = detectar_outliers(df)

    lluvia_ref = 0.0
    if "lluvia_dia" in df.columns:
        lluvia_ref = float(df["lluvia_dia"].max(skipna=True) or 0)
    elif "lluvia_total" in df.columns:
        lluvia_ref = float(df["lluvia_total"].iloc[-1] or 0)

    summary = {
        "total_registros": int(len(df)),
        "registros_imputados": int(nulos_iniciales),
        "fecha_inicio": native(df["fecha_lectura"].min()) if "fecha_lectura" in df.columns else None,
        "fecha_fin": native(df["fecha_lectura"].max()) if "fecha_lectura" in df.columns else None,
        "temp_promedio": native(round(df["temp_externa"].mean(), 2)) if "temp_externa" in df.columns else 0,
        "temp_minima": native(round(df["temp_externa"].min(), 2)) if "temp_externa" in df.columns else 0,
        "temp_maxima": native(round(df["temp_externa"].max(), 2)) if "temp_externa" in df.columns else 0,
        "humedad_promedio": native(round(df["humedad_externa"].mean(), 1)) if "humedad_externa" in df.columns else 0,
        "lluvia_total": native(round(lluvia_ref, 2)),
        "vel_viento_promedio": native(round(df["viento_vel"].mean(), 2)) if "viento_vel" in df.columns else 0,
        "outliers_consenso": int(df["outlier_consenso"].sum()) if "outlier_consenso" in df.columns else 0,
    }

    last_record = {}
    if len(df) > 0:
        last = df.iloc[-1]
        last_record = {
            "fecha_lectura": native(last.get("fecha_lectura")),
            "temp_externa": native(last.get("temp_externa")),
            "humedad_externa": native(last.get("humedad_externa")),
            "viento_vel": native(last.get("viento_vel")),
            "vel_viento": native(last.get("viento_vel")),
            "lluvia_hora": native(last.get("lluvia_hora")),
            "lluvia_dia": native(last.get("lluvia_dia")),
            "lluvia_24h": native(last.get("lluvia_dia")),
            "lluvia_semana": native(last.get("lluvia_semana")),
            "lluvia_mes": native(last.get("lluvia_mes")),
            "lluvia_total": native(last.get("lluvia_total")),
            "viento_dir": None if pd.isna(last.get("viento_dir")) else str(last.get("viento_dir")),
            "direccion_viento": None if pd.isna(last.get("viento_dir")) else str(last.get("viento_dir")),
            "punto_rocio": native(last.get("punto_rocio")),
            "sensacion_termica": native(last.get("sensacion_termica")),
        }

    preview_cols = [
        "fecha_lectura",
        "temp_externa",
        "humedad_externa",
        "viento_vel",
        "viento_dir",
        "lluvia_dia",
        "punto_rocio",
        "sensacion_termica",
        "outlier_consenso",
    ]
    preview_df = df[[c for c in preview_cols if c in df.columns]].tail(20)
    preview = []
    for _, row in preview_df.iterrows():
        preview.append({k: native(row[k]) for k in preview_df.columns})

    forecast = calcular_pronostico_horario(df)

    csv_cols = [c for c in COLUMNAS_DB + [
        "outlier_iqr", "outlier_zscore", "outlier_isolation_forest", "cluster_dbscan", "outlier_consenso"
    ] if c in df.columns]
    csv_path = str(Path(output_file).with_suffix(".csv"))
    df_csv = df[csv_cols].copy()
    df_csv["fecha_lectura"] = pd.to_datetime(df_csv["fecha_lectura"]).dt.strftime("%Y-%m-%d %H:%M:%S")
    df_csv.to_csv(csv_path, index=False)

    escribir_excel(df, output_file, summary, outliers)

    print(json.dumps({
        "summary": summary,
        "last_record": last_record,
        "outliers": outliers,
        "preview": preview,
        "csv_file": csv_path,
        "forecast": forecast,
        "python_executable": sys.executable,
    }))


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Argumentos insuficientes"}), file=sys.stderr)
        sys.exit(1)
    try:
        preprocesar(sys.argv[1], sys.argv[2])
    except Exception as exc:
        print(json.dumps({"error": str(exc)}), file=sys.stderr)
        sys.exit(1)