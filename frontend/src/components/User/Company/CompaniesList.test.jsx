import { render, screen } from "@testing-library/react";
import { ActiveCompaniesList as CompaniesList } from "./ActiveCompaniesList";

jest.mock("@/actions/company", () => ({
  archiveCompanyAction: jest.fn(),
}));

jest.mock("next/navigation", () => ({
  useRouter: () => ({ push: jest.fn(), refresh: jest.fn() }),
}));

jest.mock("sonner", () => ({
  toast: {
    success: jest.fn(),
    error: jest.fn(),
  },
}));

jest.mock("next/link", () => {
  /** @param {{ children: import("react").ReactNode; href: string }} props */
  function MockLink({ children, href, ...rest }) {
    return (
      <a href={href} {...rest}>
        {children}
      </a>
    );
  }
  MockLink.displayName = "MockLink";
  return {
    __esModule: true,
    default: MockLink,
  };
});

jest.mock("lucide-react", () => ({
  Building2: (props) => <svg data-testid="building-icon" {...props} />,
  PlusCircle: (props) => <svg data-testid="plus-icon" {...props} />,
  Users: (props) => <svg data-testid="users-icon" {...props} />,
  GraduationCap: (props) => <svg data-testid="graduation-icon" {...props} />,
  Archive: (props) => <svg data-testid="archive-icon" {...props} />,
  Settings: (props) => <svg data-testid="settings-icon" {...props} />,
}));

const mockCompanies = [
  {
    id: "11111111-1111-1111-1111-111111111111",
    name: "ООО «Альфа»",
    inn: "7707083893",
    status: "ACTIVE",
    workersCount: 3,
    protocolsCount: 5,
  },
  {
    id: "22222222-2222-2222-2222-222222222222",
    name: "ИП Иванов",
    inn: "771234567890",
    status: "ACTIVE",
    workersCount: 0,
    protocolsCount: 0,
  },
];

describe("CompaniesList", () => {
  it("renders company cards when companies are provided", () => {
    render(<CompaniesList companies={mockCompanies} />);

    expect(screen.getByTestId("companies-grid")).toBeInTheDocument();

    expect(screen.getByTestId(`company-card-${mockCompanies[0].id}`)).toBeInTheDocument();
    expect(screen.getByTestId(`company-card-${mockCompanies[1].id}`)).toBeInTheDocument();
  });

  it("renders company names and INN badges", () => {
    render(<CompaniesList companies={mockCompanies} />);

    expect(screen.getByText(mockCompanies[0].name)).toBeInTheDocument();
    expect(screen.getByText(mockCompanies[1].name)).toBeInTheDocument();

    expect(screen.getByText(`ИНН ${mockCompanies[0].inn}`)).toBeInTheDocument();
    expect(screen.getByText(`ИНН ${mockCompanies[1].inn}`)).toBeInTheDocument();
  });

  it("links each card to the company workspace and settings", () => {
    render(<CompaniesList companies={mockCompanies} />);

    const links = screen.getAllByRole("link");
    const workspaceLinks = links.filter(
      (link) =>
        link.getAttribute("href") === `/user/company/${mockCompanies[0].id}` ||
        link.getAttribute("href") === `/user/company/${mockCompanies[1].id}`
    );
    expect(workspaceLinks).toHaveLength(2);

    const settingsLinks = screen.getAllByRole("link", { name: /Настройки/i });
    expect(settingsLinks).toHaveLength(2);
    expect(settingsLinks[0]).toHaveAttribute(
      "href",
      `/user/company/${mockCompanies[0].id}/settings`
    );
    expect(settingsLinks[1]).toHaveAttribute(
      "href",
      `/user/company/${mockCompanies[1].id}/settings`
    );
  });

  it("renders empty state when companies array is empty", () => {
    render(<CompaniesList companies={[]} />);

    expect(screen.getByTestId("companies-empty")).toBeInTheDocument();
    expect(screen.getByText(/Компании не найдены/)).toBeInTheDocument();
  });

  it("renders link to create company in empty state", () => {
    render(<CompaniesList companies={[]} />);

    const createLink = screen.getByRole("link", { name: /Добавить компанию/i });
    expect(createLink).toHaveAttribute("href", "/user/company/add");
  });
});
