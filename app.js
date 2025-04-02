const mongoose = require('mongoose');
require('dotenv').config();

mongoose.connect(process.env.MONGO_URI)
  .then(() => console.log("MongoDB connected"))
  .catch((err) => console.log(err));

const productRoutes = require('./routes/product'); 
app.use(express.json()); 
app.use('/products', productRoutes); 

const express = require('express');
const app = express();
const PORT = process.env.PORT || 3000;

// Middleware to parse JSON bodies
app.use(express.json());

// Start the server
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});

app.post('/products/add', (req, res) => {
    const { name, price } = req.body;
    // Here you would typically save the product to a database
    res.json({ message: 'Product added', product: { name, price } });
});