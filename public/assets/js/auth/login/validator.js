export function validateLoginInputs({ studentId, password }) {
  studentId = studentId.trim();
  password = password.trim();

  if (!studentId) {
    return {
      valid: false,
      message: "Please enter your Student ID.",
    };
  }

  if (!/^[A-Za-z0-9-]+$/.test(studentId)) {
    return {
      valid: false,
      message: "Student ID contains invalid characters.",
    };
  }

  if (!password) {
    return {
      valid: false,
      message: "Please enter your password.",
    };
  }

  return {
    valid: true,
    studentId,
    password,
  };
}
