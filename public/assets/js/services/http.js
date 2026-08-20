export async function post(url, data) {
  const options = {
    method: "POST",
  };

  if (data instanceof FormData) {
    options.body = data;
  } else {
    options.headers = {
      "Content-Type": "application/json",
    };

    options.body = JSON.stringify(data);
  }

  const response = await fetch(`${window.API_URL}${url}`, options);

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result.message || "Request failed.");
  }

  return result;
}

export async function get(url) {
  const response = await fetch(`${window.API_URL}${url}`, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  });

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result.message || "Request failed.");
  }

  return result;
}
