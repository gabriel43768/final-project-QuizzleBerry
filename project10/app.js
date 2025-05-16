const express = require('express');
const path = require('path');

// Create the Express app
const app = express();
const port = 3000;

// Set up view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Simple route that doesn't use views
app.get('/test', (req, res) => {
  res.send('Test route is working!');
});

// Home route using a view
app.get('/', (req, res) => {
  res.render('home');
});

// Start the server
app.listen(port, () => {
  console.log(`Server running at http://localhost:${port}`);
});
