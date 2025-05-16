const  express = require('express');
bodyParser = require('body-parser');
app = express();

const PORT = 5000;

app.use(bodyParser.urlencoded({ extended: false }));

app.get('/', (req, res) => {
    console.log(req.query);
    res.send(__dirname + '/index.html');
});

app.post('/', (req, res) => {
console.log (req.body);
    res.send('Thank you for your submission!');
});

app.put('/', (req, res) => {
    console.log(req.body);
    res.send('Thank you for your submission!');
});

app.delete('/', (req, res) => {
    res.send('Thank you for your submission!');
});

app.listen(PORT, () => {
    console.log('Server is running on ' + PORT);
    })