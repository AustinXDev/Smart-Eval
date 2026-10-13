function apiUrl(path) {
  return `${String(window.API_URL).replace(/\/+$/, "")}/${String(path).replace(/^\/+/, "")}`;
}

let csrfRequest;

async function getCsrfToken() {
  if (!csrfRequest) {
    csrfRequest = fetch(apiUrl("csrf.php"), {
      method: "GET",
      credentials: "same-origin",
      headers: { Accept: "application/json" },
      cache: "no-store",
    })
      .then(async (response) => {
        const result = await response.json();
        if (!response.ok || !result.token)
          throw new Error("Unable to initialize security token.");
        return result.token;
      })
      .catch((error) => {
        csrfRequest = null;
        throw error;
      });
  }
  return csrfRequest;
}

export async function post(url, data) {
  const options = {
    method: "POST",
    credentials: "same-origin",
    headers: { "X-CSRF-Token": await getCsrfToken() },
  };

  if (data instanceof FormData) {
    options.body = data;
  } else {
    options.headers = {
      ...options.headers,
      "Content-Type": "application/json",
    };

    options.body = JSON.stringify(data);
  }

  const response = await fetch(apiUrl(url), options);

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result.message || (typeof result.error === "string" ? result.error : "Request failed."));
  }

  return result;
}

export async function get(url) {
  const response = await fetch(apiUrl(url), {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  });

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result.message || (typeof result.error === "string" ? result.error : "Request failed."));
  }

  return result;
}
