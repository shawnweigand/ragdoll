from flask import Flask, jsonify, request
from dotenv import load_dotenv
import threading
import os
from utils.load_and_split_docs import load_and_split_docs

# Loaders
from loaders.drive_loader import load_drive_folder_docs
from loaders.strong_csv_loader import load_strong_csv_docs

# Splitters
from splitters.recursive_splitter import split_recursive_docs
from splitters.strong_csv_splitter import split_strong_csv_docs

# Extractors
from extractors.google_drive_extractor import google_drive_extractor
from extractors.strong_csv_extractor import strong_csv_extractor

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
    # Extract meta and tags from the request
    data = request.get_json()
    meta = data.get("meta", [])
    tags = data.get("tags", {})
    
    # Load and split documents in a separate thread
    threading.Thread(target=load_and_split_docs, args=(
        folder_id,              #Arg: parent_id
        "GoogleDrive",          #Arg: document_type
        load_drive_folder_docs, #Arg: load_fn
        [folder_id],            #Arg: load_params
        split_recursive_docs,   #Arg: split_fn
        google_drive_extractor, #Arg: extract_fn
        meta,                   #Arg: meta
        tags                    #Arg: tags
    )).start()
    
    return jsonify({"message": "Documents are being processed."})

# Strong CSV file
@app.route('/api/strong/<csv_name>', methods=['POST'])
def parse_strong_docs(csv_name: str):
    """
    Parse documents from a Strong CSV file.
    
    Args:
        csv_name (str): The name of the Strong CSV file.
        
    Returns:
        str: A message indicating the status of the operation.
    """
    # Extract meta and tags from the request
    data = request.get_json()
    meta = data.get("meta", [])
    tags = data.get("tags", {})

    # Check if the CSV file exists
    csv = f"{csv_name}.csv"
    if not os.path.exists(f".csv/{csv}"):
        return jsonify({"error": f"File {csv_name}.csv not found."}), 404

    # Load and split documents in a separate thread
    threading.Thread(target=load_and_split_docs, args=(
        None,                       #Arg: parent_id
        "StrongCSV",                #Arg: document_type
        load_strong_csv_docs,       #Arg: load_fn
        [csv],                      #Arg: load_params
        split_strong_csv_docs,      #Arg: split_fn
        strong_csv_extractor,       #Arg: extract_fn
        meta,                       #Arg: meta
        tags                        #Arg: tags
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