from flask import Flask, jsonify, request
from dotenv import load_dotenv
from loaders.drive_loader import load_drive_folder_docs
from splitters.recursive_splitter import split_recursive_docs
import threading

# Load environment variables
load_dotenv()

# Create Flask app
app = Flask(__name__)

def load_and_split_docs(load_fn, load_params, split_fn):
    """
    Load and split documents using the provided functions and parameters.
    
    Args:
        load_fn (callable): Function to load documents.
        load_params (dict): Parameters for the loading function.
        split_fn (callable): Function to split documents.
    """

    try:
        # Load documents
        docs = load_fn(*load_params)
    except Exception as e:
        with (open("error.log", "a")) as f:
            f.write(f"Error loading documents: {e}\n")
        return
    
    try:
        # Split documents
        texts = split_fn(docs)
    except Exception as e:
        with (open("error.log", "a")) as f:
            f.write(f"Error splitting documents: {e}\n")
        return

    with (open("output.log", "a")) as f:
        f.write(texts[0].page_content)
    return

# Google Drive folder
@app.route('/api/drive/folder/<folder_id>', methods=['POST']) # folder_id="1wvLgaZsQJs0RUWTIUvZ3-xxQQVY5fIsc"
def parse_drive_folder_docs(folder_id: str):
    """
    Parse documents from a Google Drive folder.
    
    Args:
        folder_id (str): The ID of the Google Drive folder.
        
    Returns:
        str: A message indicating the status of the operation.
    """
    # Load and split documents in a separate thread
    threading.Thread(target=load_and_split_docs, args=(load_drive_folder_docs, [folder_id], split_recursive_docs)).start()
    
    return jsonify({"message": "Documents are being processed."})



# # Root route
# @app.route('/')
# def hello_world():
#     return 'Hello, World!'

# # Example: Simple JSON API endpoint
# @app.route('/api/ping', methods=['GET'])
# def ping():
#     return jsonify({'message': 'pong'})

# # Example: Echo back posted data
# @app.route('/api/echo', methods=['POST'])
# def echo():
#     data = request.get_json()
#     return jsonify({
#         'you_sent': data
#     })

# # Example: Dynamic URL parameter
# @app.route('/api/user/<username>', methods=['GET'])
# def get_user(username):
#     return jsonify({'user': username})

# # Example: Query parameters
# @app.route('/api/search', methods=['GET'])
# def search():
#     query = request.args.get('q')
#     return jsonify({'search_query': query})

# Main entry point
if __name__ == '__main__':
    app.run(debug=True)