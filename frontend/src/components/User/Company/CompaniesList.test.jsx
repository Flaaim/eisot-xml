import { render, screen } from "@testing-library/react";
import { CompaniesList } from "./ActiveCompaniesList";

// Mock next/link
jest.mock("next/link", () => {
  const MockLink = ({ children, href, ...rest }) => {
    return (
      <a href={href} {...rest}>
        {children}
      </a>
    );
  };
  MockLink.displayName = "MockLink";
  return {
    __esModule: true,
    default: MockLink,
  };
});

// Mock lucide-react icons
jest.mock("lucide-react", () => ({
  Building2: (props) => <svg data-testid="building-icon" {...props} />,
  PlusCircle: (props) => <svg data-testid="plus-icon" {...props} />,
}));

const mockCompanies = [
  {
    id: "11111111-1111-1111-1111-111111111111",
    name: "\u041e\u041e\u041e \u00ab\u0410\u043b\u044c\u0444\u0430\u00bb",
    inn: "7707083893",
  },
  {
    id: "22222222-2222-2222-2222-222222222222",
    name: "\u0418\u041f \u0418\u0432\u0430\u043d\u043e\u0432",
    inn: "771234567890",
  },
];

describe("CompaniesList", () => {
  it("renders company cards when companies are provided", () => {
    render(<CompaniesList companies={mockCompanies} />);

    expect(screen.getByTestId("companies-grid")).toBeInTheDocument();

    expect(
      screen.getByTestId(`company-card-${mockCompanies[0].id}`),
    ).toBeInTheDocument();
    expect(
      screen.getByTestId(`company-card-${mockCompanies[1].id}`),
    ).toBeInTheDocument();
  });

  it("renders company names and INN badges", () => {
    render(<CompaniesList companies={mockCompanies} />);

    expect(screen.getByText(mockCompanies[0].name)).toBeInTheDocument();
    expect(screen.getByText(mockCompanies[1].name)).toBeInTheDocument();

    expect(screen.getByText(`\u0418\u041d\u041d ${mockCompanies[0].inn}`)).toBeInTheDocument();
    expect(screen.getByText(`\u0418\u041d\u041d ${mockCompanies[1].inn}`)).toBeInTheDocument();
  });

  it("wraps each card in a link to the company context", () => {
    render(<CompaniesList companies={mockCompanies} />);

    const links = screen.getAllByRole("link");
    const companyLinks = links.filter((link) =>
      link.getAttribute("href")?.startsWith("/user/company/"),
    );

    expect(companyLinks).toHaveLength(2);
    expect(companyLinks[0]).toHaveAttribute(
      "href",
      `/user/company/${mockCompanies[0].id}`,
    );
    expect(companyLinks[1]).toHaveAttribute(
      "href",
      `/user/company/${mockCompanies[1].id}`,
    );
  });

  it("renders empty state when companies array is empty", () => {
    render(<CompaniesList companies={[]} />);

    expect(screen.getByTestId("companies-empty")).toBeInTheDocument();
    expect(screen.getByText(/\u041a\u043e\u043c\u043f\u0430\u043d\u0438\u0438 \u043d\u0435 \u043d\u0430\u0439\u0434\u0435\u043d\u044b/)).toBeInTheDocument();
  });

  it("renders link to create company in empty state", () => {
    render(<CompaniesList companies={[]} />);

    const createLink = screen.getByRole("link", { name: /\u0421\u043e\u0437\u0434\u0430\u0442\u044c \u043a\u043e\u043c\u043f\u0430\u043d\u0438\u044e/i });
    expect(createLink).toHaveAttribute("href", "/user/company/add");
  });
});
