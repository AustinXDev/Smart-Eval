export function validateAdminInputs({
  usernameInput = "",
  passwordInput = "",
}) {
  const username = usernameInput.trim();
  const password = passwordInput;

  console.log("Validator username:", username);
  console.log("Validator password:", password);

  if (username === "" && password === "") {
    return {
      valid: false,
      message: "Please enter all fields.",
    };
  }

  if (username === "") {
    return {
      valid: false,
      message: "Please enter username.",
    };
  }

  if (password === "") {
    return {
      valid: false,
      message: "Please enter password.",
    };
  }

  return {
    valid: true,
    username,
    password,
  };
}
