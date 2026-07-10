import type { CompanyShort } from "@/types/company";

interface CompanyCardProps {
  readonly company: CompanyShort;
}

/**
 * Карточка компании для дашборда.
 *
 * Отображает название и ИНН. Стилизована с glassmorphism-эффектом,
 * плавным hover-состоянием и градиентной акцентной полосой.
 */
export function CompanyCard({ company }: CompanyCardProps) {
  return (
    <article
      data-testid={`company-card-${company.id}`}
      className="
        group relative overflow-hidden rounded-2xl
        border border-white/10 bg-white/5 p-6
        backdrop-blur-xl transition-all duration-300 ease-out
        hover:-translate-y-1 hover:border-indigo-500/30
        hover:bg-white/10
        hover:shadow-[0_8px_32px_rgba(99,102,241,0.15)]
      "
    >
      {/* Gradient accent bar */}
      <div
        className="
          absolute inset-x-0 top-0 h-1
          bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500
          opacity-0 transition-opacity duration-300
          group-hover:opacity-100
        "
        aria-hidden="true"
      />

      {/* Company icon */}
      <div
        className="
          mb-4 flex size-12 items-center justify-center rounded-xl
          bg-gradient-to-br from-indigo-500/20 to-purple-500/20 text-indigo-400
          transition-colors duration-300 group-hover:from-indigo-500/30
          group-hover:to-purple-500/30
        "
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          className="size-6"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          strokeWidth={1.5}
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
          />
        </svg>
      </div>

      {/* Company name */}
      <h3
        className="
          mb-2 text-lg leading-tight font-semibold text-white
          transition-colors duration-300
          group-hover:text-indigo-200
        "
      >
        {company.name}
      </h3>

      {/* INN badge */}
      <div className="flex items-center gap-2">
        <span
          className="
            inline-flex items-center rounded-lg
            bg-slate-800/60 px-3 py-1
            text-xs font-medium tracking-wide text-slate-400
            ring-1 ring-slate-700/50
          "
        >
          ИНН {company.inn}
        </span>
      </div>

      {/* Hover arrow */}
      <div
        className="
          absolute top-1/2 right-5 -translate-x-2
          -translate-y-1/2 opacity-0 transition-all
          duration-300 group-hover:translate-x-0
          group-hover:opacity-100
        "
        aria-hidden="true"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          className="size-5 text-indigo-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          strokeWidth={2}
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"
          />
        </svg>
      </div>
    </article>
  );
}
