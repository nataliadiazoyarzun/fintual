const LOCALE = 'es-CL';

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
              <th>Action</th>
              <th>Amount</th>
              <th>Shares</th>
            </tr>
          </thead>
          <tbody>
            {recommendations.map((rec) => (
              <tr key={`${rec.action}-${rec.ticker}`}>
                <td>{rec.ticker}</td>
                <td>{rec.action}</td>
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
