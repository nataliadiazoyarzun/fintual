import { useEffect, useRef, useState } from 'react';

const FIELDS = [
  { name: 'ticker', label: 'Ticker', type: 'text', col: 'col-12 col-md-3' },
  { name: 'quantity', label: 'Cantidad', type: 'number', col: 'col-6 col-md-2' },
  { name: 'price', label: 'Precio', type: 'number', col: 'col-6 col-md-2' },
  { name: 'target', label: 'Objetivo (%)', type: 'number', col: 'col-6 col-md-3' },
];

export default function PortfolioForm({ rows, onChange, onSubmit, loading }) {
  const [focusId, setFocusId] = useState(null);
  const tickerInputs = useRef({});

  // After adding a row, move keyboard focus to its ticker field.
  useEffect(() => {
    if (focusId !== null) {
      tickerInputs.current[focusId]?.focus();
      setFocusId(null);
    }
  }, [focusId]);

  function updateRow(id, field, value) {
    onChange(rows.map((row) => (row.id === id ? { ...row, [field]: value } : row)));
  }

  function removeRow(id) {
    onChange(rows.filter((row) => row.id !== id));
  }

  function addRow() {
    const row = newRow();
    onChange([...rows, row]);
    setFocusId(row.id);
  }

  function handleSubmit(event) {
    event.preventDefault();
    onSubmit();
  }

  return (
    <form onSubmit={handleSubmit}>
      {/* Column headers for wide screens; on mobile each field shows its own label. */}
      <div className="row g-2 mb-2 fw-semibold d-none d-md-flex" aria-hidden="true">
        {FIELDS.map((field) => (
          <div key={field.name} className={field.col}>
            {field.label}
          </div>
        ))}
      </div>

      {rows.map((row, index) => (
        <div
          key={row.id}
          role="group"
          aria-label={`Fila ${index + 1}`}
          className="row g-2 align-items-end pb-3 mb-3 border-bottom"
        >
          {FIELDS.map((field) => (
            <div key={field.name} className={field.col}>
              <span className="d-block small text-muted mb-1 d-md-none" aria-hidden="true">
                {field.label}
              </span>
              <input
                className="form-control"
                aria-label={`${field.label}, fila ${index + 1}`}
                type={field.type}
                step={field.type === 'number' ? 'any' : undefined}
                autoComplete={field.name === 'ticker' ? 'off' : undefined}
                ref={
                  field.name === 'ticker'
                    ? (el) => {
                        tickerInputs.current[row.id] = el;
                      }
                    : undefined
                }
                value={row[field.name]}
                onChange={(e) => updateRow(row.id, field.name, e.target.value)}
              />
            </div>
          ))}
          <div className="col-6 col-md-2 text-end">
            <button
              type="button"
              className="btn btn-outline-danger"
              aria-label={`Eliminar fila ${index + 1}`}
              onClick={() => removeRow(row.id)}
            >
              Eliminar
            </button>
          </div>
        </div>
      ))}

      <div className="d-flex justify-content-between gap-2">
        <button type="button" className="btn btn-outline-secondary" onClick={addRow}>
          Agregar fila
        </button>
        <button type="submit" className="btn btn-primary" disabled={loading}>
          {loading ? 'Calculando...' : 'Rebalancear'}
        </button>
      </div>
    </form>
  );
}

let nextId = 1;

export function newRow(values = {}) {
  return { id: nextId++, ticker: '', quantity: '', price: '', target: '', ...values };
}
