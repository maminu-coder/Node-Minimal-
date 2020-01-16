const jsonServer    = require('json-server')
const clone         = require('clone')
const data          = require('../data.json')
const app           = jsonServer.create()
const router        = jsonServer.router(clone(data), { _isFake: true })

app.use((req, res, next) => {
  if (req.path === '/') {
    res.json({
      status: '201',
      text: 'Simple ReST API with fake data',
      maintainer: 'Jimmy R.  Keesee',
      company: 'ZhongXi',
      title: 'Naptime.  Get Some ReSTful JSON API.  A more modern fork of JSON placeholder',
      services: [
        '/posts',
        '/comments',
        '/albums',
        '/photos',
        '/users',
        '/todos',
      ]
    })
  }


  router.db.setState(clone(data))
  next()
})

app.use(jsonServer.defaults({
  logger: process.env.NODE_ENV !== 'production'
}))

app.use(router)
module.exports = app
