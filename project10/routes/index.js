const express = require('express');
const router = express.Router();

/* GET home page. */
router.get('/', function(req, res) {
  // Try this instead
  res.render('pages/home', { title: 'QUIZZLEBERRY' });
});

// Add this to your routes/index.js
router.get('/test', function(req, res) {
    res.send('Test route is working!');
  });
  
module.exports = router;