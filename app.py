from flask import Flask, jsonify, request
from dotenv import load_dotenv
from loaders.drive_loader import load_drive_folder_docs
from splitters.recursive_splitter import split_recursive_docs
from extractors.google_drive_extractor import google_drive_extractor
from utils.load_and_split_docs import load_and_split_docs
import threading

# Load environment variables
load_dotenv()

# Create Flask app
app = Flask(__name__)

# Google Drive folder
@app.route('/api/drive/folder/<folder_id>', methods=['POST'])
def parse_drive_folder_docs(folder_id: str):
    """
    Parse documents from a Google Drive folder.
    
    Args:
        folder_id (str): The ID of the Google Drive folder.
        
    Returns:
        str: A message indicating the status of the operation.
    """
    # Load and split documents in a separate thread
    threading.Thread(target=load_and_split_docs, args=(
        folder_id,              #Arg: parent_id
        "GoogleDrive",          #Arg: document_type
        load_drive_folder_docs, #Arg: load_fn
        [folder_id],            #Arg: load_params
        split_recursive_docs,   #Arg: split_fn
        google_drive_extractor  #Arg: extract_fn
    )).start()
    
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