from __future__ import annotations

import os
import sys
import tempfile
import unittest
from copy import deepcopy
from datetime import date, timedelta
from pathlib import Path
from unittest.mock import patch


TEST_DIR = os.path.dirname(os.path.abspath(__file__))
AGENT_ROOT = os.path.normpath(os.path.join(TEST_DIR, ".."))
sys.path.insert(0, os.path.join(AGENT_ROOT, "pydeps"))
sys.path.insert(0, os.path.join(AGENT_ROOT, "scripts"))

import reporte_cobranza as rc


def estado_cuenta_con_pagos(
    *,
    lunes_objetivo: date,
    montos: list[float],
    cuota: float = 551.0,
    saldo_vencido: float = 0.0,
) -> dict:
    """Estado de cuenta mínimo compatible con el algoritmo real de reparto."""
    return {
        "statusCredito": "Vigente",
        "cuota": cuota,
        "datosCliente": {"nombreCliente": "CLIENTE PRUEBA"},
        "datosSaldos": {"saldoTotalVencido": saldo_vencido},
        "datosNotasCargos": [],
        "datosCargos": [
            {
                "idCargo": 100,
                "concepto": "CUOTA SEMANAL",
                "monto": cuota,
                "fechaVencimiento": lunes_objetivo.isoformat(),
            },
            {
                "idCargo": 101,
                "concepto": "CUOTA SEMANAL",
                "monto": cuota,
                "fechaVencimiento": (lunes_objetivo + timedelta(days=7)).isoformat(),
            },
        ],
        "datosPagos": [
            {
                "idPago": 9000 + idx,
                "fechaRegistro": f"2026-07-25 0{idx}:00:00",
                "montoPago": monto,
                "extemporaneos": 0,
                "numeroCuotaSemanal": "100,101",
            }
            for idx, monto in enumerate(montos, start=1)
        ],
    }


class LunesCuotaObjetivoTest(unittest.TestCase):
    def test_viernes_sabado_domingo_protegen_lunes_siguiente(self) -> None:
        esperado = date(2026, 7, 27)
        for fecha_pago in (date(2026, 7, 24), date(2026, 7, 25), date(2026, 7, 26)):
            with self.subTest(fecha_pago=fecha_pago):
                self.assertEqual(rc.lunes_cuota_objetivo_para_pago(fecha_pago), esperado)

    def test_lunes_a_jueves_protegen_lunes_de_la_misma_semana(self) -> None:
        esperado = date(2026, 7, 27)
        for fecha_pago in (
            date(2026, 7, 27),
            date(2026, 7, 28),
            date(2026, 7, 29),
            date(2026, 7, 30),
        ):
            with self.subTest(fecha_pago=fecha_pago):
                self.assertEqual(rc.lunes_cuota_objetivo_para_pago(fecha_pago), esperado)

    def test_viernes_cambia_al_lunes_de_la_semana_nueva(self) -> None:
        self.assertEqual(
            rc.lunes_cuota_objetivo_para_pago(date(2026, 7, 31)),
            date(2026, 8, 3),
        )


class SaldoFinSemanaTest(unittest.TestCase):
    def test_cuota_incompleta_no_produce_saldo(self) -> None:
        lunes = date(2026, 7, 27)
        ec = estado_cuenta_con_pagos(lunes_objetivo=lunes, montos=[550])
        self.assertEqual(rc.calcular_saldo_a_favor(ec, lunes), 0.0)

    def test_cuota_exacta_no_produce_saldo(self) -> None:
        lunes = date(2026, 7, 27)
        ec = estado_cuenta_con_pagos(lunes_objetivo=lunes, montos=[551])
        self.assertEqual(rc.calcular_saldo_a_favor(ec, lunes), 0.0)

    def test_pago_750_deja_solo_199_despues_de_cuota(self) -> None:
        lunes = date(2026, 7, 27)
        ec = estado_cuenta_con_pagos(lunes_objetivo=lunes, montos=[750])
        self.assertEqual(rc.calcular_saldo_a_favor(ec, lunes), 199.0)

    def test_pago_1279_deja_728_despues_de_cuota(self) -> None:
        lunes = date(2026, 7, 27)
        ec = estado_cuenta_con_pagos(lunes_objetivo=lunes, montos=[1279])
        self.assertEqual(rc.calcular_saldo_a_favor(ec, lunes), 728.0)

    def test_pagos_acumulados_cubren_cuota_y_dejan_solo_remanente(self) -> None:
        lunes = date(2026, 7, 27)
        ec = estado_cuenta_con_pagos(lunes_objetivo=lunes, montos=[300, 400])
        self.assertEqual(rc.calcular_saldo_a_favor(ec, lunes), 149.0)

    def test_saldo_vencido_bloquea_aplicacion(self) -> None:
        lunes = date(2026, 7, 27)
        ec = estado_cuenta_con_pagos(
            lunes_objetivo=lunes,
            montos=[1279],
            saldo_vencido=2,
        )
        self.assertEqual(rc.calcular_saldo_a_favor(ec, lunes), 0.0)

    def test_cargo_del_lunes_objetivo_ausente_bloquea_aplicacion(self) -> None:
        lunes = date(2026, 7, 27)
        ec = estado_cuenta_con_pagos(lunes_objetivo=lunes, montos=[1279])
        ec["datosCargos"][0]["fechaVencimiento"] = "2026-07-20"
        self.assertEqual(rc.calcular_saldo_a_favor(ec, lunes), 0.0)

    def test_reporte_domingo_marca_aplicar_tras_cubrir_lunes(self) -> None:
        fecha_pago = date(2026, 7, 25)  # sábado
        lunes = rc.lunes_cuota_objetivo_para_pago(fecha_pago)
        ec = estado_cuenta_con_pagos(lunes_objetivo=lunes, montos=[1279])
        row = {
            "id_credito": 1737107,
            "id_cliente": 985311,
            "valor_real": 750.0,
            "fecha_ultimo_pago_efectivo": fecha_pago.isoformat(),
            "monto_abono_efectivo": -1279.0,
        }
        with patch.object(rc, "consultar_s2", return_value={"estadoCuenta": ec}):
            resultado = rc.procesar_registro(
                row,
                fecha_pago.isoformat(),
                lunes,
                fecha_pago,
                date(2026, 7, 26),  # ejecución domingo
            )

        self.assertIsNotNone(resultado)
        self.assertEqual(resultado["SALDO_A_FAVOR"], 728.0)
        self.assertEqual(resultado["SALDO_APLICABLE_GC"], 728.0)
        self.assertIn(rc.COMENTARIO_APLICAR, resultado["COMENTARIOS"])
        self.assertNotIn(rc.COMENTARIO_CUOTA_CUBIERTA, resultado["COMENTARIOS"])


class PipelineRegresionTest(unittest.TestCase):
    def setUp(self) -> None:
        self.base = {
            "ID_CREDITO": 1737107,
            "ID_CLIENTE": 985311,
            "NOMBRE_CLIENTE": "CLIENTE PRUEBA",
            "STATUS_CREDITO": "Vigente",
            "CUOTA_SEMANAL": 551.0,
            "DEUDA_GC": 750.0,
            "SALDO_A_FAVOR": 728.0,
            "SALDO_APLICABLE_GC": 728.0,
            "FECHA_ULTIMO_ABONO_EFECTIVO": "2026-07-25 — sábado",
            "ULTIMO_ABONO_EFECTIVO": -1279.0,
            "MAXI_APP_CONECTO": "Sí",
            "COMENTARIOS": rc.COMENTARIO_APLICAR,
            "ERROR": "",
        }

    def test_sobrante_sabado_permanece_en_excel_domingo(self) -> None:
        salida, stats = rc.aplicar_pipeline_final_excel_gc(
            [deepcopy(self.base)],
            date(2026, 7, 25),
        )
        self.assertEqual(stats["final"], 1)
        self.assertEqual(salida[0]["SALDO_APLICABLE_GC"], 728.0)

    def test_regla_maxi_app_no_se_modifica(self) -> None:
        fila = deepcopy(self.base)
        fila["MAXI_APP_CONECTO"] = "No"
        salida, stats = rc.aplicar_pipeline_final_excel_gc([fila], date(2026, 7, 25))
        self.assertEqual(salida, [])
        self.assertEqual(stats["excl_no_rango"], 1)

    def test_limite_por_ultimo_abono_no_se_modifica(self) -> None:
        fila = deepcopy(self.base)
        fila["ULTIMO_ABONO_EFECTIVO"] = -560.0
        salida, stats = rc.aplicar_pipeline_final_excel_gc([fila], date(2026, 7, 25))
        self.assertEqual(stats["ajuste_no_alcanza"], 1)
        self.assertEqual(salida[0]["SALDO_APLICABLE_GC"], 560.0)

    def test_minimo_estricto_mayor_200_no_se_modifica(self) -> None:
        fila = deepcopy(self.base)
        fila["SALDO_A_FAVOR"] = 200.0
        fila["SALDO_APLICABLE_GC"] = 200.0
        salida, stats = rc.aplicar_pipeline_final_excel_gc([fila], date(2026, 7, 25))
        self.assertEqual(salida, [])
        self.assertEqual(stats["excl_saldo_final"], 1)

    def test_marca_legacy_no_aplicar_sigue_excluida(self) -> None:
        fila = deepcopy(self.base)
        fila["COMENTARIOS"] = rc.COMENTARIO_CUOTA_CUBIERTA
        salida, stats = rc.aplicar_pipeline_final_excel_gc([fila], date(2026, 7, 25))
        self.assertEqual(salida, [])
        self.assertEqual(stats["excl_cuota_siguiente_cubierta"], 1)


class ValidacionCarteraProtegidaTest(unittest.TestCase):
    @staticmethod
    def fila(extemporaneos: float, credito: int = 1) -> dict:
        return {
            "ID_CREDITO": credito,
            "SALDO_APLICABLE_GC": 250.0,
            "_EXTEMPORANEOS_S2_DIA": extemporaneos,
            "_PAGOS_FECHA_VALOR_S2": 1,
        }

    def test_activa_la_regla_cuando_s2_entrega_senal_suficiente(self) -> None:
        filas = [self.fila(250.0) for _ in range(364)]
        filas.extend(self.fila(0.0) for _ in range(49))
        salida, stats = rc.aplicar_validacion_cartera_extemporaneos_lote(filas)
        self.assertEqual(stats["modo"], "validacion_cartera_activa")
        self.assertEqual(stats["excluidos"], 49)
        self.assertEqual(len(salida), 364)

    def test_lote_sistemicamente_en_cero_usa_pipeline_historico(self) -> None:
        filas = [self.fila(0.0) for _ in range(2_978)]
        salida, stats = rc.aplicar_validacion_cartera_extemporaneos_lote(filas)
        self.assertEqual(stats["modo"], "fallback_pipeline_historico")
        self.assertEqual(stats["excluidos"], 0)
        self.assertEqual(len(salida), 2_978)

    def test_un_credito_rechazado_se_revalua_y_puede_entrar_despues(self) -> None:
        primera_corrida = [self.fila(250.0, credito) for credito in range(1, 10)]
        primera_corrida.append(self.fila(0.0, 999))
        salida_primera, stats_primera = (
            rc.aplicar_validacion_cartera_extemporaneos_lote(primera_corrida)
        )
        self.assertEqual(stats_primera["modo"], "validacion_cartera_activa")
        self.assertNotIn(999, {fila["ID_CREDITO"] for fila in salida_primera})

        siguiente_corrida = [
            self.fila(250.0, credito) for credito in range(1, 10)
        ]
        siguiente_corrida.append(self.fila(250.0, 999))
        salida_siguiente, stats_siguiente = (
            rc.aplicar_validacion_cartera_extemporaneos_lote(siguiente_corrida)
        )
        self.assertEqual(stats_siguiente["modo"], "validacion_cartera_activa")
        self.assertIn(999, {fila["ID_CREDITO"] for fila in salida_siguiente})

    def test_suma_solo_extemporaneos_de_la_fecha_de_negocio(self) -> None:
        ec = {
            "datosPagos": [
                {"idPago": 1, "fechaValor": "2026-07-28", "extemporaneos": 125},
                {"idPago": 2, "fechaValor": "2026-07-28", "extemporaneos": 125},
                {"idPago": 3, "fechaValor": "2026-07-27", "extemporaneos": 999},
            ]
        }
        metricas = rc.metricas_extemporaneos_fecha_negocio(
            ec,
            date(2026, 7, 28),
        )
        self.assertEqual(metricas["total"], 250.0)
        self.assertEqual(metricas["pagos_fecha"], 2)


class RegeneracionDescargoTest(unittest.TestCase):
    def test_regeneracion_lee_checkpoint_previo_sin_reconfirmarlo(self) -> None:
        with tempfile.TemporaryDirectory() as tmp:
            principal = Path(tmp) / "guia_descargo.json"
            respaldo = Path(tmp) / "guia_descargo.json.bak"
            principal.write_text('{"ultimo_id_tabla": 103}', encoding="utf-8")
            respaldo.write_text('{"ultimo_id_tabla": 0}', encoding="utf-8")

            with patch.object(
                rc, "_guia_descargo_path", return_value=str(principal)
            ), patch.object(
                rc, "_guia_descargo_backup_path", return_value=str(respaldo)
            ), patch.object(
                rc, "REGENERAR_REPORTE", True
            ):
                guia = rc._leer_guia_descargo()

        self.assertIsNotNone(guia)
        self.assertEqual(guia["ultimo_id_tabla"], 0)


class ProteccionExcelVacioTest(unittest.TestCase):
    def test_aborta_si_hubo_candidatos_y_la_salida_queda_vacia(self) -> None:
        with self.assertRaisesRegex(RuntimeError, "anti-Excel vacío"):
            rc.validar_salida_no_vacia(2_993, [])

    def test_permite_una_salida_no_vacia(self) -> None:
        rc.validar_salida_no_vacia(2_993, [{"ID_CREDITO": 123}])

    def test_permite_cero_cuando_no_hubo_candidatos(self) -> None:
        rc.validar_salida_no_vacia(0, [])


if __name__ == "__main__":
    unittest.main()
