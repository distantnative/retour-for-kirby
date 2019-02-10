export default function (code) {
  if (code === 'disabled') {
    return 'disabled'
  }

  if (parseInt(code) >= 300 && parseInt(code) < 400) {
    return 'redirect';
  }

  return 'notice';
}
