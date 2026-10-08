export function inputValidation(studentId, studentEmail) {
  if (!studentId || !studentEmail) {
    return {
      valid: false,
      message: "Student ID and student email are required.",
    };
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!emailRegex.test(studentEmail)) {
    return {
      valid: false,
      message: "Please enter valid email address.",
    };
  }

  return {
    valid: true,
  };
}
