module.exports = {
  input: 'src/main.js',
  bundleNodeModules: true,
  output: {
    dir: './',
    fileName: 'index.js',
    format: 'umd-min',
    sourceMap: false,
    minify: 'false'
  },
  plugins: {
    vue: true,
    // livereload: true
  }
}
