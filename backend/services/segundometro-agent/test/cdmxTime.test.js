const test = require('node:test');
const assert = require('node:assert/strict');

const { _test } = require('../lib/cdmxTime');

test('acepta una fuente remota cercana al reloj local', () => {
  const local = { fecha: '2026-08-04', horaSeg: '07:00:30' };
  const remote = {
    fecha: '2026-08-04',
    hora: '07:00',
    horaSeg: '07:00:00',
    source: 'fuente-prueba',
    fromRemote: true,
  };

  const result = _test.validateRemoteClock(remote, local);
  assert.equal(result.clockSkewMs, -30000);
});

test('rechaza el desfase de veinte minutos observado en produccion', () => {
  const local = { fecha: '2026-08-04', horaSeg: '07:00:00' };
  const stale = {
    fecha: '2026-08-04',
    hora: '06:40',
    horaSeg: '06:40:00',
    source: 'timeapi.io',
    fromRemote: true,
  };

  assert.throws(
    () => _test.validateRemoteClock(stale, local),
    /rechazada por desfase de -1200 s/
  );
});
