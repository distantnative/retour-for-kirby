
export default function(array) {
  return array.map(x => {
    if (x.last) {
      x.last = x.last.replace(/-/g, "/");
    }
    return x;
  });
}
