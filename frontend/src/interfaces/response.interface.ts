export interface ApiResponse<T = any> {
  ok: boolean;
  data?: T | null;
  error?: string;
}
