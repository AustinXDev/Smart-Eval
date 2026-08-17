export function validateInputs({ adminUsername }) {
  const username = adminUsername.trim();

  if (username === "") {
    return {
      valid: false,
      message: "Please enter all fields.",
    };
  }

  return {
    valid: true,
    username: username,
  };
}
