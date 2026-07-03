import { calculateSnilsChecksum, isValidSnils, normalizeSnils, toSnilsApiValue } from "@/lib/snils";

describe("snils", () => {
  it("normalizes digits to EISOT format", () => {
    expect(normalizeSnils("11223344595")).toBe("112-233-445 95");
    expect(normalizeSnils("112-233-445 95")).toBe("112-233-445 95");
  });

  it("validates checksum", () => {
    expect(isValidSnils("112-233-445 95")).toBe(true);
    expect(isValidSnils("112-233-445 99")).toBe(false);
  });

  it("skips checksum for exempt numbers", () => {
    expect(isValidSnils("001-001-001 00")).toBe(true);
  });

  it("returns api-ready value", () => {
    expect(toSnilsApiValue("11223344595")).toBe("112-233-445 95");
    expect(toSnilsApiValue("112-233-445 99")).toBeNull();
  });

  it("calculates checksum", () => {
    expect(calculateSnilsChecksum("112233445")).toBe(95);
  });
});
