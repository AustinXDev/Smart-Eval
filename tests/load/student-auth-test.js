import http from "k6/http";
import { check } from "k6";

const API_BASE = (__ENV.API_BASE || "").replace(/\/+$/, "");
const RUN_LOGIN = __ENV.RUN_LOGIN === "true";

export const options = {
  scenarios: {
    student_auth: {
      executor: "shared-iterations",
      vus: 1,
      iterations: 1,
      maxDuration: "20s",
    },
  },

  thresholds: {
    http_req_failed: ["rate<0.05"],
    checks: ["rate==1"],
  },
};

export default function () {
  if (!API_BASE) {
    throw new Error("API_BASE is required.");
  }

  console.log(`CSRF started: ${philippineTime()}`);
  // STEP 1: Get CSRF token.
  const csrfResponse = http.get(`${API_BASE}/csrf.php`, {
    headers: {
      Accept: "application/json",
    },
    timeout: "10s",
    tags: { name: "csrf" },
  });

  let csrfData;

  try {
    csrfData = csrfResponse.json();
  } catch (_) {
    throw new Error("Invalid CSRF JSON response.");
  }

  if (
    !check(csrfResponse, {
      "CSRF HTTP 200": (r) => r.status === 200,
      "CSRF token available": () =>
        typeof csrfData.token === "string" && csrfData.token.length > 0,
    })
  ) {
    return;
  }

  console.log(`CSRF completed: ${philippineTime()}`);

  // STEP 2: Optional authentication test.
  if (!RUN_LOGIN) {
    console.log("CSRF test passed. Login disabled.");
    return;
  }

  const studentId = __ENV.TEST_STUDENT_ID;
  const password = __ENV.TEST_PASSWORD;

  if (!studentId || !password) {
    throw new Error("Test credentials are missing.");
  }

  console.log(`Login started: ${philippineTime()}`);

  const loginResponse = http.post(
    `${API_BASE}/student/auth/login.php`,
    JSON.stringify({
      student_id: studentId,
      password: password,
    }),
    {
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": csrfData.token,
        Accept: "application/json",
      },
      timeout: "15s",
      tags: { name: "student-login" },
    },
  );

  let loginData;

  try {
    loginData = loginResponse.json();
  } catch (_) {
    throw new Error("Invalid login JSON response.");
  }

  check(loginResponse, {
    "Login HTTP 200": (r) => r.status === 200,
    "Credentials accepted": () => loginData.status === "2fa_required",
  });

  console.log(`Login status: ${String(loginData.status)}`);

  console.log(
    `Authentication response: ${loginResponse.timings.duration.toFixed(0)}ms`,
  );
  console.log(`Login completed: ${philippineTime()}`);
}

function philippineTime() {
  const now = new Date();

  return new Date(now.getTime() + 8 * 60 * 60 * 1000)
    .toISOString()
    .replace("T", " ")
    .replace("Z", " PHT");
}
