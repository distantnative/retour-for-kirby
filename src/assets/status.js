export default function (code) {
  if (code === "disabled") {
    return "no"
  }

  if (parseInt(code) >= 300 && parseInt(code) < 400) {
    return "yes";
  }

  return "mmm";
}
