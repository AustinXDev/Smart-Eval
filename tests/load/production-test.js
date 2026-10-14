import http from "k6/http";
import { check, sleep } from "k6";

export const options = {
  scenarios: {
    login_test: {
      executor: "ramping-vus",
      startVUs: 0,
      stages: [
        { duration: "15s", target: 10 },
        { duration: "30s", target: 10 },
        { duration: "15s", target: 0 },
      ],
      gracefulRampDown: "5s",
    },
  },

  thresholds: {
    http_req_failed: [
      {
        threshold: "rate<0.05",
        abortOnFail: true,
        delayAbortEval: "10s",
      },
    ],
    http_req_duration: [
      {
        threshold: "p(95)<2000",
        abortOnFail: true,
        delayAbortEval: "10s",
      },
    ],
  },
};

export default function () {
  const response = http.get("https://smart-eval.com/login", { timeout: "10s" });

  check(response, {
    "Login page loaded": (r) => r.status === 200,
    "Response under 2 seconds": (r) => r.timings.duration < 2000,
  });

  sleep(3);
}
