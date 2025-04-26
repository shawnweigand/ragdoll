from flask import Flask, jsonify, request
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Create Flask app
app = Flask(__name__)

# Root route
@app.route('/')
def hello_world():
    return 'Hello, World!'

# Example: Simple JSON API endpoint
@app.route('/api/ping', methods=['GET'])
def ping():
    return jsonify({'message': 'pong'})

# Example: Echo back posted data
@app.route('/api/echo', methods=['POST'])
def echo():
    data = request.get_json()
    return jsonify({
        'you_sent': data
    })

# Example: Dynamic URL parameter
@app.route('/api/user/<username>', methods=['GET'])
def get_user(username):
    return jsonify({'user': username})

# Example: Query parameters
@app.route('/api/search', methods=['GET'])
def search():
    query = request.args.get('q')
    return jsonify({'search_query': query})

# Main entry point
if __name__ == '__main__':
    app.run(debug=True)