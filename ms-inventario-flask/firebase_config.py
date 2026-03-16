import firebase_admin
from firebase_admin import credentials, db

DATABASE_URL = "https://taller-ventas-microservicios-default-rtdb.firebaseio.com/"

if not firebase_admin._apps:
    cred = credentials.Certificate("serviceAccountKey.json")
    firebase_admin.initialize_app(cred, {
        "databaseURL": DATABASE_URL
    })

products_ref = db.reference("products")
