import { z } from "zod";

import { isValidInn, normalizeInn } from "@/lib/inn";

/** Форма настроек агрегата Company (Title + Inn). */
export const CompanySettingsSchema = z.object({
  title: z
    .string()
    .min(1, "Наименование (Title) обязательно для заполнения.")
    .max(500, "Наименование не должно превышать 500 символов."),
  inn: z
    .string()
    .min(1, "ИНН (Inn) обязателен для заполнения.")
    .transform(normalizeInn)
    .superRefine((val, ctx) => {
      if (val.length !== 10 && val.length !== 12) {
        ctx.addIssue({
          code: "custom",
          message: "ИНН должен состоять из 10 или 12 цифр.",
        });
        return;
      }

      if (!isValidInn(val)) {
        ctx.addIssue({
          code: "custom",
          message: "Неверная контрольная сумма ИНН.",
        });
      }
    }),
});

export type CompanySettingsFormData = z.infer<typeof CompanySettingsSchema>;
