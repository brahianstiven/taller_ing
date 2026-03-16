require('dotenv').config();

const express = require('express');
const mongoose = require('mongoose');
const Sale = require('./models/Sale');

const app = express();

app.use(express.json());

mongoose.connect(process.env.MONGO_URI)
  .then(() => {
    console.log('MongoDB conectado');
  })
  .catch((error) => {
    console.error('Error conectando MongoDB:', error.message);
  });

app.get('/health', (req, res) => {
  res.status(200).json({ status: 'ok', service: 'express-ventas' });
});

app.post('/sales', async (req, res) => {
  try {
    const sale = await Sale.create(req.body);
    return res.status(201).json(sale);
  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
});

app.get('/sales', async (req, res) => {
  try {
    const sales = await Sale.find().sort({ createdAt: -1 });
    return res.status(200).json(sales);
  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
});

app.get('/sales/user/:userId', async (req, res) => {
  try {
    const sales = await Sale.find({ user_id: req.params.userId }).sort({ createdAt: -1 });
    return res.status(200).json(sales);
  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
});

app.get('/sales/date/:date', async (req, res) => {
  try {
    const start = new Date(req.params.date);
    const end = new Date(req.params.date);
    end.setDate(end.getDate() + 1);

    const sales = await Sale.find({
      sale_date: {
        $gte: start,
        $lt: end
      }
    }).sort({ sale_date: -1 });

    return res.status(200).json(sales);
  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
});

app.listen(process.env.PORT, () => {
  console.log(`Servidor corriendo en puerto ${process.env.PORT}`);
});
