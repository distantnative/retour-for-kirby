

export default function (doc, id) {
  let ctx = doc.getElementById(id).getContext('2d');

  let blue = ctx.createLinearGradient(0, 0, 0, 225);
  blue.addColorStop(0, 'rgba(66, 113, 174, 1)');
  blue.addColorStop(0.7, 'rgba(66, 113, 174, 0.5)');
  blue.addColorStop(1, 'rgba(255, 255, 255, 0.25)');

  let grey = ctx.createLinearGradient(0, 0, 0, 500);
  grey.addColorStop(0, 'rgba(175, 175, 175, 1)');
  grey.addColorStop(0.25, 'rgba(204, 204, 204, 0.5)');
  grey.addColorStop(1, 'rgba(255, 255, 255, 0)');

  return {
    blue: blue,
    grey: grey
  }
}
