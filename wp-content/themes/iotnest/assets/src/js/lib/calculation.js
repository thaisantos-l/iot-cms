/** Fórmula do OEE: dados permanecem no navegador, sem telemetria. */
export function calculate(values) {
  const read = value => typeof value === 'number' ? value : Number(String(value).trim().replace(',', '.'));
  const rate = read(values.rate), hours = read(values.hours), days = read(values.days), atual = read(values.atual), iot = read(values.iot);
  const fields = {rate,hours,days,atual,iot};
  for (const [key,value] of Object.entries(fields)) if (!Number.isFinite(value) || String(values[key]).trim() === '' || values[key] === null || values[key] === undefined) return {error:'invalid',field:key};
  if (rate <= 0) return {error:'positive',field:'rate'};
  if (hours <= 0 || hours > 24) return {error:'invalid',field:'hours'};
  if (!Number.isInteger(days) || days < 1 || days > 31) return {error:'invalid',field:'days'};
  if (atual < 0 || atual > 100) return {error:'invalid',field:'atual'};
  if (iot < 0 || iot > 100) return {error:'invalid',field:'iot'};
  if (iot < atual) return {error:'target',field:'iot'};
  const total = rate * hours * days;
  if (!Number.isFinite(total)) return {error:'invalid',field:'rate'};
  return {total,atual:{oee:atual,loss:total*(1-atual/100)},iot:{oee:iot,loss:total*(1-iot/100)}};
}
