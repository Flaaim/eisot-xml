import {cookies} from "next/headers";
import {RefreshSessionAction} from "@/actions/auth";


export async function apiFetch(url: string, options: RequestInit = {}): Promise<Response> {
  const { headers: customHeaders, ...restOptions } = options;

  const cookieStore = await cookies();
  const access_token = cookieStore.get("access_token")?.value;

  const headers = new Headers(customHeaders);

  if(access_token){
    headers.set("Authorization", `Bearer ${access_token}`);
  }

  const response = await fetch(`${process.env.INTERNAL_BACKEND_URL}${url}`, {
    ...restOptions,
    headers: Object.fromEntries(headers.entries()),
  })

  if (response.status !== 401) {
    return response;
  }

  const refreshResult = await RefreshSessionAction()

  if(!refreshResult.success || !refreshResult.access_token){
    return response;
  }

  if(refreshResult.success ){
    const new_access_token = String(refreshResult.access_token);
    headers.set("Authorization", `Bearer ${new_access_token}`);

    return await fetch(`${process.env.INTERNAL_BACKEND_URL}${url}`, {
      ...restOptions,
      headers: Object.fromEntries(headers.entries()),
    });
  }

}
