import "@testing-library/jest-dom";
import { render, screen } from "@testing-library/react";
import Home from "./Home";

jest.mock("next/link", () => {
  const MockLink = ({ children, href, ...rest }) => (
    <a href={href} {...rest}>
      {children}
    </a>
  );
  MockLink.displayName = "MockLink";
  return { __esModule: true, default: MockLink };
});

describe("Home landing page", () => {
  it("renders hero heading", () => {
    render(<Home />);

    expect(
      screen.getByRole("heading", {
        level: 1,
        name: /Подготовка реестров для ЕИСОТ без ошибок/i,
      }),
    ).toBeInTheDocument();
  });

  it("renders primary call to action", () => {
    render(<Home />);

    const cta = screen.getByRole("button", { name: /Создать реестр/i });
    expect(cta).toHaveAttribute("href", "/user/company");
  });

  it("renders core domain benefits", () => {
    render(<Home />);

    expect(screen.getByText("Автоматическая проверка данных")).toBeInTheDocument();
    expect(screen.getByText("До 5 000 сотрудников в одном файле")).toBeInTheDocument();
    expect(screen.getByText("Полное соответствие требованиям Минтруда")).toBeInTheDocument();
  });

  it("renders user journey steps", () => {
    render(<Home />);

    expect(screen.getByText("Укажите данные организации")).toBeInTheDocument();
    expect(screen.getByText("Добавьте сотрудников")).toBeInTheDocument();
    expect(screen.getByText("Сформируйте файл в один клик")).toBeInTheDocument();
    expect(screen.getByText("Загрузите в личный кабинет Минтруда")).toBeInTheDocument();
  });
});
