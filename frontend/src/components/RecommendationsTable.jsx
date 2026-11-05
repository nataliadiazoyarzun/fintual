const LOCALE = 'es-CL';

// Display labels only; the API contract keeps BUY/SELL.
const ACTION_LABELS = { BUY: 'Comprar', SELL: 'Vender' };
const ACTION_BADGES = { BUY: 'text-bg-success', SELL: 'text-bg-danger' };

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
    <section className="card shadow-sm">
      <h2 className="card-header h6 mb-0 fw-semibold">Resultado</h2>
      <div className="card-body">
        <p className="mb-3">
          Valor total del portafolio: <strong>{amountFormat.format(totalValue)}</strong>
        </p>

        {recommendations.length === 0 ? (
          <div className="alert alert-success mb-0">
            Tu portafolio ya está balanceado. No hay operaciones recomendadas.
          </div>
        ) : (
          <div className="table-responsive">
            <table className="table table-hover align-middle mb-0">
              <caption className="visually-hidden">Operaciones recomendadas</caption>
              <thead className="table-light">
                <tr>
                  <th scope="col">Ticker</th>
                  <th scope="col">Acción</th>
                  <th scope="col" className="text-end">Monto</th>
                  <th scope="col" className="text-end">Cantidad de acciones</th>
                </tr>
              </thead>
              <tbody>
                {recommendations.map((rec) => (
                  <tr key={`${rec.action}-${rec.ticker}`}>
                    <th scope="row" className="fw-semibold">{rec.ticker}</th>
                    <td>
                      <span className={`badge ${ACTION_BADGES[rec.action] ?? 'text-bg-secondary'}`}>
                        {ACTION_LABELS[rec.action] ?? rec.action}
                      </span>
                    </td>
                    <td className="text-end">{amountFormat.format(rec.amount)}</td>
                    <td className="text-end">{sharesFormat.format(rec.shares)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </section>
  );
}
