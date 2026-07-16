import { NextRequest, NextResponse } from "next/server";
import { RefreshSessionAction } from "@/actions/auth";

async function refreshTokens(
  request: NextRequest,
  refreshToken: string
): Promise<NextResponse | null> {
  try {
    const newTokens = await RefreshSessionAction(refreshToken);

    if (newTokens === null) {
      const redirectResponse = NextResponse.redirect(new URL("/join/login", request.url));
      redirectResponse.cookies.delete("access_token");
      redirectResponse.cookies.delete("refresh_token");
      return redirectResponse;
    }

    const requestHeaders = new Headers(request.headers);
    requestHeaders.set("x-access-token", newTokens.access_token);

    const response = NextResponse.next({
      request: { headers: requestHeaders },
    });

    response.cookies.set({
      name: "access_token",
      value: newTokens.access_token,
      httpOnly: true,
      path: "/",
      secure: process.env.NODE_ENV === "production",
      maxAge: newTokens.expires_in,
    });

    response.cookies.set({
      name: "refresh_token",
      value: newTokens.refresh_token,
      httpOnly: true,
      path: "/",
      secure: process.env.NODE_ENV === "production",
      maxAge: 2592000,
    });

    return response;
  } catch {
    return NextResponse.redirect(new URL("/join/login", request.url));
  }
}

export async function middleware(request: NextRequest) {
  const accessToken = request.cookies.get("access_token")?.value;
  const refreshToken = request.cookies.get("refresh_token")?.value;

  const { pathname } = request.nextUrl;
  const isProtected = pathname.startsWith("/user") || pathname.startsWith("/admin");

  if (isProtected && !accessToken && !refreshToken) {
    return NextResponse.redirect(new URL("/join/login", request.url));
  }

  const requestHeaders = new Headers(request.headers);
  const response = NextResponse.next({
    request: {
      headers: requestHeaders,
    },
  });

  if (isProtected && !accessToken && refreshToken) {
    const refreshed = await refreshTokens(request, refreshToken);
    if (refreshed) {
      return refreshed;
    }
  }

  if (pathname === "/join/login" && (accessToken || refreshToken)) {
    return NextResponse.redirect(new URL("/user/company", request.url));
  }

  return response;
}

export const config = {
  matcher: ["/user/:path*", "/admin/:path*", "/join/login"],
};
