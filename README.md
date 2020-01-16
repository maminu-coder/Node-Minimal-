Run me!  Just do the following

1. git clone https://code.keesee.net/cohack/node-minimal.git naptime
2. cd naptime
3. npm install
4. npm start


Running this app is simple.  Clone, enter (cd) into the cloned project, install dependencies and then get started via the npm start command.  You then have a number of routes available to you as a ReSTful JSON API.   The '/' home route gives you some basic information about available routes.  Get, Post, c


## Features

* No registration
* Zero-config
* Basic API
* "Has many" relationships
* Filters and nested resources
* Cross-domain ([CORS](http://en.wikipedia.org/wiki/Cross-origin_resource_sharing) and [JSONP](http://en.wikipedia.org/wiki/JSONP))
* Supports GET, POST, PUT, PATCH, DELETE and OPTIONS verbs
* HTTP or HTTPS
* Compatible with React, Angular, Vue, Ember, ...





## Guide

You can use this project with any type of project that needs to get JSON data (React, Vue, Angular, Node, Rails, Swift, Android, ...).

Below you'll find examples using [Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API). You can copy paste them in your browser Console to quickly test and play.

## Get a resource

```js
fetch('/posts/1')
  .then(response => response.json())
  .then(json => console.log(json))
// Output
{
  id: 1,
  title: '[...]',
  body: '[...]',
  userId: 1
}
```


## List all resources

```js
fetch('/posts')
  .then(response => response.json())
  .then(json => console.log(json))

// Output
[
  { id: 1, title: '[...]' /* ... */ },
  /* ... */
  { id: 100, title: '[...]' /* ... */ }
]
```

## Create a resource

```js
fetch('/posts', {
    method: 'POST',
    body: JSON.stringify({
      title: 'foo',
      body: 'bar',
      userId: 1
    }),
    headers: {
      "Content-type": "application/json; charset=UTF-8"
    }
  })
  .then(response => response.json())
  .then(json => console.log(json))

// Output
{
  id: 101,
  title: 'foo',
  body: 'bar',
  userId: 1
}
```

Important: the resource will not be really created on the server but it will be faked as if. In other words, if you try to access a post using 101 as an id, you'll get a 404 error.

## Update a resource

```js
fetch('posts/1', {
    method: 'PUT',
    body: JSON.stringify({
      id: 1,
      title: 'foo',
      body: 'bar',
      userId: 1
    }),
    headers: {
      "Content-type": "application/json; charset=UTF-8"
    }
  })
  .then(response => response.json())
  .then(json => console.log(json))

// Output
{
  id: 1,
  title: 'foo',
  body: 'bar',
  userId: 1
}
```

```js
fetch('posts/1', {
    method: 'PATCH',
    body: JSON.stringify({
      title: 'foo'
    }),
    headers: {
      "Content-type": "application/json; charset=UTF-8"
    }
  })
  .then(response => response.json())
  .then(json => console.log(json))

// Output
{
  id: 1,
  title: 'foo',
  body: '[...]',
  userId: 1
}
```

Important: the resource will not be really updated on the server but it will be faked as if. 

## Delete a resource

```js
fetch('posts/1', {
  method: 'DELETE'
})
```

Important: the resource will not be really deleted on the server but it will be faked as if. 

## Filtering resources

Basic filtering is supported through query parameters.

```js
// Will return all the posts that belong to the first user
fetch('/posts?userId=1')
  .then(response => response.json())
  .then(json => console.log(json))
```

## Nested resources

One level of nested route is available.

```js
// Equivalent to /comments?postId=1
fetch('/1/comments')
  .then(response => response.json())
  .then(json => console.log(json))
```

Available nested routes:

* /posts/1/comments
* /albums/1/photos
* /users/1/albums
* /users/1/todos
* /users/1/posts