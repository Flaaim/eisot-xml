const isServer = typeof window === "undefined";
const BASE_URL = isServer
  ? process.env.INTERNAL_BACKEND_URL || process.env.NEXT_PUBLIC_BACKEND_URL || "http://api"
  : process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8081";

export const API = {
  auth: {
    joinByEmail: () => BASE_URL + `/v1/auth/join/request`,
    login: () => BASE_URL + `/token`,
    refreshToken: () => BASE_URL + `/token`,
    revokeToken: () => BASE_URL + `/v1/auth/token/revoke`,
    joinConfirm: () => BASE_URL + `/v1/auth/join/confirm`,
    passwordResetRequest: () => BASE_URL + `/v1/auth/password/reset/request`,
    passwordResetConfirm: () => BASE_URL + `/v1/auth/password/reset`,
    requestEmailChange: () => BASE_URL + `/v1/auth/email/change/request`
  },
  user: {
    profile: () => BASE_URL + `/v1/user/profile`
  },
  company: {
    add: () => BASE_URL + `/v1/companies`,
    list: () => BASE_URL + `/v1/companies`,
    get: (id: string) => BASE_URL + `/v1/companies/${id}`,
    archive: (id: string) => BASE_URL + `/v1/companies/${id}/archive`,
    restore: (id: string) => BASE_URL + `/v1/companies/${id}/restore`,
    trainingRecords: (id: string) => BASE_URL + `/v1/companies/${id}/training-records`,
    stats: (id: string) => BASE_URL + `/v1/companies/${id}/stats`,
    rename: (id: string) => BASE_URL + `/v1/companies/${id}/name`,
    changeInn: (id: string) => BASE_URL + `/v1/companies/${id}/inn`,
  },
  worker: {
    register: (companyId: string) => BASE_URL + `/v1/companies/${companyId}/workers`,
    recordTraining: (workerId: string) => BASE_URL + `/v1/workers/${workerId}/training-records`,
  },
  training: {
    export: () => BASE_URL + `/v1/training/export`,
  }
};
