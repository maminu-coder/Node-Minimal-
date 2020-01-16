const app   = require('./src/app')
const port  = 3333

app.listen(port, () => {
  console.log('Naptime listening on http://localhost:' + port)
})
