import NotFound from "./not-found";
import { render, screen } from "@testing-library/react";

describe("Not-found", () => {
  it("renders not found page and home link", () => {
    render(<NotFound />);

    const heading = screen.getByRole("heading", { level: 1 });
    expect(heading).toBeInTheDocument();

    const textHeading = screen.getByText("Страница не найдена");
    expect(textHeading).toBeInTheDocument();

    const homeLink = screen.getByRole("link", { name: /Вернуться на главную/i });
    expect(homeLink).toBeInTheDocument();
    expect(homeLink).toHaveAttribute("href", "/");
  });
});
