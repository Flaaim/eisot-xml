import { render, screen, waitFor } from "@testing-library/react";
import AddCompanyForm from "./AddCompanyForm";
import userEvent from "@testing-library/user-event";
import { addCompanyAction } from "../../../actions/company";

jest.mock("../../../actions/company", () => {
  const mockFn = jest.fn();
  return {
    __esModule: true,
    addCompanyAction: mockFn,
  };
});

jest.mock("sonner", () => ({
  toast: {
    success: jest.fn(),
    error: jest.fn(),
  },
}));

jest.mock("next/navigation", () => ({
  useRouter: () => ({ push: jest.fn(), refresh: jest.fn() }),
}));

const mockAddCompanyAction = jest.mocked(addCompanyAction);

describe("AddCompanyForm", () => {
  const defaultProps = {
    plan: null,
    totalCompanyCount: 0,
  };

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("renders all fields and the submit button", () => {
    render(<AddCompanyForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: "Добавить компанию" })).toBeInTheDocument();
    expect(screen.getByLabelText(/Название организации/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/ИНН/i)).toBeInTheDocument();
  });

  it("submits with correct data on happy path", async () => {
    const user = userEvent.setup();
    mockAddCompanyAction.mockResolvedValue({ ok: true, data: { id: "test-uuid" } });

    render(<AddCompanyForm {...defaultProps} />);

    const nameInput = screen.getByLabelText(/Название организации/i);
    const innInput = screen.getByLabelText(/ИНН/i);
    const submitButton = screen.getByRole("button", { name: "Добавить компанию" });

    await user.type(nameInput, "ООО Тест");
    await user.type(innInput, "7707083893");
    await user.click(submitButton);

    await waitFor(() => {
      expect(mockAddCompanyAction).toHaveBeenCalledWith({
        name: "ООО Тест",
        inn: "7707083893",
      });
    });
  });

  it("resets the form after successful submission", async () => {
    const user = userEvent.setup();
    mockAddCompanyAction.mockResolvedValue({ ok: true, data: { id: "test-uuid" } });

    render(<AddCompanyForm {...defaultProps} />);

    const nameInput = screen.getByLabelText(/Название организации/i);
    const innInput = screen.getByLabelText(/ИНН/i);
    const submitButton = screen.getByRole("button", { name: "Добавить компанию" });

    await user.type(nameInput, "ООО Тест");
    await user.type(innInput, "7707083893");
    await user.click(submitButton);

    await waitFor(() => {
      expect(nameInput).toHaveValue("");
      expect(innInput).toHaveValue("");
    });
  });

  it("shows validation error for INN with non-digit characters", async () => {
    const user = userEvent.setup();

    render(<AddCompanyForm {...defaultProps} />);

    const innInput = screen.getByLabelText(/ИНН/i);
    const nameInput = screen.getByLabelText(/Название организации/i);

    await user.type(innInput, "12345abcde");
    await user.click(nameInput); // trigger onBlur

    expect(await screen.findByText(/ИНН должен состоять из 10 или 12 цифр/i)).toBeInTheDocument();

    expect(mockAddCompanyAction).not.toHaveBeenCalled();
  });

  it("shows validation error for INN with invalid checksum", async () => {
    const user = userEvent.setup();

    render(<AddCompanyForm {...defaultProps} />);

    const innInput = screen.getByLabelText(/ИНН/i);
    const nameInput = screen.getByLabelText(/Название организации/i);

    await user.type(innInput, "1234567890");
    await user.click(nameInput); // trigger onBlur

    expect(await screen.findByText(/Неверная контрольная сумма ИНН/i)).toBeInTheDocument();

    expect(mockAddCompanyAction).not.toHaveBeenCalled();
  });

  it("shows validation error for INN with wrong digit count (9 digits)", async () => {
    const user = userEvent.setup();

    render(<AddCompanyForm {...defaultProps} />);

    const innInput = screen.getByLabelText(/ИНН/i);
    const nameInput = screen.getByLabelText(/Название организации/i);

    await user.type(innInput, "123456789");
    await user.click(nameInput); // trigger onBlur

    expect(await screen.findByText(/ИНН должен состоять из 10 или 12 цифр/i)).toBeInTheDocument();

    expect(mockAddCompanyAction).not.toHaveBeenCalled();
  });

  it("shows validation error when name is empty", async () => {
    const user = userEvent.setup();

    render(<AddCompanyForm {...defaultProps} />);

    const nameInput = screen.getByLabelText(/Название организации/i);
    const innInput = screen.getByLabelText(/ИНН/i);

    await user.click(nameInput);
    await user.click(innInput); // trigger onBlur on name

    expect(
      await screen.findByText(/Название организации обязательно для заполнения/i)
    ).toBeInTheDocument();

    expect(mockAddCompanyAction).not.toHaveBeenCalled();
  });

  it("shows server error from backend", async () => {
    const user = userEvent.setup();
    mockAddCompanyAction.mockResolvedValue({
      ok: false,
      error: "Компания с таким ИНН уже существует.",
    });

    render(<AddCompanyForm {...defaultProps} />);

    const nameInput = screen.getByLabelText(/Название организации/i);
    const innInput = screen.getByLabelText(/ИНН/i);
    const submitButton = screen.getByRole("button", { name: "Добавить компанию" });

    await user.type(nameInput, "ООО Тест");
    await user.type(innInput, "7707083893");
    await user.click(submitButton);

    expect(await screen.findByText(/Компания с таким ИНН уже существует/i)).toBeInTheDocument();
  });

  it("accepts a valid 12-digit INN", async () => {
    const user = userEvent.setup();
    mockAddCompanyAction.mockResolvedValue({ ok: true, data: { id: "test-uuid-2" } });

    render(<AddCompanyForm {...defaultProps} />);

    const nameInput = screen.getByLabelText(/Название организации/i);
    const innInput = screen.getByLabelText(/ИНН/i);
    const submitButton = screen.getByRole("button", { name: "Добавить компанию" });

    await user.type(nameInput, "ИП Иванов");
    await user.type(innInput, "500100732259");
    await user.click(submitButton);

    await waitFor(() => {
      expect(mockAddCompanyAction).toHaveBeenCalledWith({
        name: "ИП Иванов",
        inn: "500100732259",
      });
    });
  });

  it("disables submit when basic plan company limit is reached", () => {
    render(<AddCompanyForm plan="basic" totalCompanyCount={1} />);

    expect(screen.getByRole("button", { name: "Добавить компанию" })).toBeDisabled();
    expect(screen.getByText(/Удалите существующую компанию или/i)).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "обновите тариф" })).toBeInTheDocument();
  });
});
