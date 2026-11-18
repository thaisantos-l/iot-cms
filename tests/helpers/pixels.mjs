/* global Image, document */
export async function pixels(page, first, second) {
  return page.evaluate(async ([a, b]) => {
    const read = async value => {
      const image = new Image();
      image.src = `data:image/png;base64,${value}`;
      await image.decode();
      const canvas = document.createElement('canvas');
      canvas.width = image.width; canvas.height = image.height;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(image, 0, 0);
      return { width: image.width, height: image.height, data: ctx.getImageData(0, 0, image.width, image.height).data };
    };
    const [x, y] = await Promise.all([read(a), read(b)]);
    if (x.width !== y.width || x.height !== y.height) return { dimensions: [x.width, x.height, y.width, y.height], changed: -1 };
    let changed = 0;
    for (let i = 0; i < x.data.length; i += 4) if ([0, 1, 2, 3].some(offset => Math.abs(x.data[i + offset] - y.data[i + offset]) > 2)) changed++;
    return { dimensions: [x.width, x.height], changed, total: x.width * x.height };
  }, [first.toString('base64'), second.toString('base64')]);
}

