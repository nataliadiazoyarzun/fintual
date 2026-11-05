export default function PortfolioForm({ rows, onChange, onSubmit, loading }) {
  function updateRow(id, field, value) {
    onChange(rows.map((row) => (row.id === id ? { ...row, [field]: value } : row)));
  }

  function removeRow(id) {
    onChange(rows.filter((row) => row.id !== id));
  }

  function addRow() {
    onChange([...rows, newRow()]);
  }

  function handleSubmit(event) {
    event.preventDefault();
    onSubmit();
  }

  return (
    <form onSubmit={handleSubmit}>
      <table>
        <thead>
          <tr>
            <th>Ticker</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Objetivo (%)</th>
            <th />
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={row.id}>
              <td>
                <input
                  aria-label="Ticker"
                  value={row.ticker}
                  onChange={(e) => updateRow(row.id, 'ticker', e.target.value)}
                />
              </td>
              <td>
                <input
                  aria-label="Cantidad"
                  type="number"
                  step="any"
                  value={row.quantity}
                  onChange={(e) => updateRow(row.id, 'quantity', e.target.value)}
                />
              </td>
              <td>
                <input
                  aria-label="Precio"
                  type="number"
                  step="any"
                  value={row.price}
                  onChange={(e) => updateRow(row.id, 'price', e.target.value)}
                />
              </td>
              <td>
                <input
                  aria-label="Objetivo (%)"
                  type="number"
                  step="any"
                  value={row.target}
                  onChange={(e) => updateRow(row.id, 'target', e.target.value)}
                />
              </td>
              <td>
                <button type="button" onClick={() => removeRow(row.id)}>
                  Eliminar
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <button type="button" onClick={addRow}>
        Agregar fila
      </button>{' '}
      <button type="submit" disabled={loading}>
        Rebalancear
      </button>
    </form>
  );
}

let nextId = 1;

export function newRow(values = {}) {
  return { id: nextId++, ticker: '', quantity: '', price: '', target: '', ...values };
}
