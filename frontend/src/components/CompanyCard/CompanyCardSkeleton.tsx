/**
 * Скелетон-плейсхолдер для карточки компании.
 *
 * Показывается во время загрузки данных — повторяет структуру CompanyCard
 * с пульсирующей анимацией.
 */
export function CompanyCardSkeleton() {
  return (
    <div
      data-testid="company-card-skeleton"
      className="
        animate-pulse rounded-2xl
        border border-white/5 bg-white/5 p-6
        backdrop-blur-xl
      "
    >
      {/* Icon placeholder */}
      <div className="mb-4 size-12 rounded-xl bg-slate-700/50" />

      {/* Title placeholder */}
      <div className="mb-3 h-5 w-3/4 rounded-lg bg-slate-700/50" />

      {/* INN badge placeholder */}
      <div className="h-6 w-32 rounded-lg bg-slate-700/30" />
    </div>
  );
}
