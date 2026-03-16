from flask import Flask, request, jsonify
from uuid import uuid4
from firebase_config import products_ref

app = Flask(__name__)

@app.get("/health")
def health():
    return jsonify({"status": "ok", "service": "flask-inventario"}), 200

@app.post("/products")
def create_product():
    data = request.get_json()

    product_id = str(uuid4())
    product = {
        "id": product_id,
        "name": data.get("name"),
        "price": data.get("price"),
        "stock": data.get("stock"),
        "description": data.get("description")
    }

    products_ref.child(product_id).set(product)
    return jsonify(product), 201

@app.get("/products")
def get_products():
    data = products_ref.get() or {}
    return jsonify(list(data.values())), 200

@app.get("/products/<product_id>")
def get_product(product_id):
    product = products_ref.child(product_id).get()

    if not product:
        return jsonify({"error": "Producto no encontrado"}), 404

    return jsonify(product), 200

@app.get("/products/<product_id>/stock")
def check_stock(product_id):
    product = products_ref.child(product_id).get()

    if not product:
        return jsonify({"error": "Producto no encontrado"}), 404

    return jsonify({
        "id": product_id,
        "stock": product.get("stock", 0)
    }), 200

@app.put("/products/<product_id>/stock")
def update_stock(product_id):
    product = products_ref.child(product_id).get()

    if not product:
        return jsonify({"error": "Producto no encontrado"}), 404

    data = request.get_json()
    new_stock = data.get("stock")

    products_ref.child(product_id).update({
        "stock": new_stock
    })

    product["stock"] = new_stock
    return jsonify(product), 200

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=8001, debug=True)
