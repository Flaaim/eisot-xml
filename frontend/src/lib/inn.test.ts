import {
  isValidInn,
  isValidInnChecksum,
  normalizeInn,
  validateInn,
} from "./inn";

describe("inn", () => {
  it("normalizes non-digit characters", () => {
    expect(normalizeInn("77-07-083-893")).toBe("7707083893");
  });

  it("validates checksum for legal entity INN", () => {
    expect(isValidInnChecksum("7707083893")).toBe(true);
    expect(isValidInnChecksum("1234567890")).toBe(false);
  });

  it("validates checksum for individual INN", () => {
    expect(isValidInnChecksum("500100732259")).toBe(true);
    expect(isValidInnChecksum("123456789012")).toBe(false);
  });

  it("validates full INN", () => {
    expect(isValidInn("7707083893")).toBe(true);
    expect(isValidInn("1234567890")).toBe(false);
  });

  it("returns validation messages", () => {
    expect(validateInn("")).toBe("ИНН обязателен для заполнения.");
    expect(validateInn("123456789")).toBe(
      "ИНН должен состоять из 10 или 12 цифр.",
    );
    expect(validateInn("1234567890")).toBe("Неверная контрольная сумма ИНН.");
  });
});
