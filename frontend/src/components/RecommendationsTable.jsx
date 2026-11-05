const LOCALE = 'es-CL';

// Display labels only; the API contract keeps BUY/SELL.
const ACTION_LABELS = { BUY: 'Comprar', SELL: 'Vender' };

const amountFormat = new Intl.NumberFormat(LOCALE, {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

const sharesFormat = new Intl.NumberFormat(LOCALE, {
  minimumFractionDigits: 0,
  maximumFractionDigits: 4,
});

// Presentation only: renders exactly what the API returned.
export default function RecommendationsTable({ result }) {
  const { totalValue, recommendations } = result;

  return (
    <section>
      <h2>Resultado</h2>
      <p>
        Valor total del portafolio: <strong>{amountFormat.format(totalValue)}</strong>
      </p>

      {recommendations.length === 0 ? (
        <p>Tu portafolio ya está balanceado. No hay operaciones recomendadas.</p>
      ) : (
        <table>
          <thead>
            <tr>
              <th>Ticker</th>
              <th>Acción</th>
              <th>Monto</th>
              <th>Cantidad de acciones</th>
            </tr>
          </thead>
          <tbody>
            {recommendations.map((rec) => (
              <tr key={`${rec.action}-${rec.ticker}`}>
                <td>{rec.ticker}</td>
                <td>{ACTION_LABELS[rec.action] ?? rec.action}</td>
                <td>{amountFormat.format(rec.amount)}</td>
                <td>{sharesFormat.format(rec.shares)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </section>
  );
}
