const mongoose = require('mongoose');

const SaleSchema = new mongoose.Schema({
  user_id: {
    type: Number,
    required: true
  },
  user_name: {
    type: String,
    required: true
  },
  products: [
    {
      product_id: {
        type: String,
        required: true
      },
      name: {
        type: String,
        required: true
      },
      quantity: {
        type: Number,
        required: true
      },
      price: {
        type: Number,
        required: true
      },
      subtotal: {
        type: Number,
        required: true
      }
    }
  ],
  total: {
    type: Number,
    required: true
  },
  sale_date: {
    type: Date,
    default: Date.now
  }
}, { timestamps: true });

module.exports = mongoose.model('Sale', SaleSchema);
