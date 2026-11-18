import { calculate } from '../lib/calculation.js';
import { scrollToElement } from '../lib/scroll.js';

export function initializeCalculator(root) {
  const form = root.querySelector('[data-calc-form]'), out = root.querySelector('[data-calc-out]');
  const error = root.querySelector('[data-calc-error]'), hint = root.querySelector('[data-calc-hint]'), cta = root.querySelector('[data-calc-cta]');
  if (!form || !out || !error) return;
  let messages;
  try { messages = JSON.parse(root.querySelector('[data-calc-messages]')?.textContent || '{}'); } catch { return; }
  form.querySelectorAll('[disabled]').forEach(element => { element.disabled = false; });
  const currency = new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL',maximumFractionDigits:0});
  const number = new Intl.NumberFormat('pt-BR',{maximumFractionDigits:2});
  form.addEventListener('submit', event => {
    event.preventDefault();
    const result = calculate(Object.fromEntries(new FormData(form)));
    error.hidden = true;
    form.querySelectorAll('[aria-invalid]').forEach(input => input.removeAttribute('aria-invalid'));
    if (result.error) {
      const field = form.elements[result.field];
      error.textContent = (messages[result.error] || messages.invalid || '').replace('{field}',field.labels[0]?.textContent || field.name);
      error.hidden = false; field.setAttribute('aria-invalid','true'); field.focus(); return;
    }
    for (const key of ['atual','iot']) {
      const row = root.querySelector(`[data-sc="${key}"]`);
      row.querySelector('[data-oee]').textContent = `${number.format(result[key].oee)}%`;
      row.querySelector('[data-loss]').textContent = currency.format(result[key].loss);
    }
    out.classList.add('is-done');
    if (hint) hint.hidden = true;
    if (cta) cta.hidden = false;
    if (matchMedia('(max-width: 960px)').matches) scrollToElement(out);
  });
}
