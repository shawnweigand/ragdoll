from services.RagdollService import RagdollService
import json

def load_and_split_docs(
        document_id,
        document_type,
        load_fn, 
        load_params, 
        split_fn
    ):
    """
    Load and split documents using the provided functions and parameters.
    
    Args:
        document_id (str): The source ID of the document to load.
        document_type (str): The type of the document (e.g., GoogleDrive).
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

    # Send to ragdoll service
    ragdoll = RagdollService()

    for i, text in enumerate(texts):
        data = {
            "name": text.metadata.get("title", "unknown"),
            "source_id": text.metadata.get("source").split("/d/")[1].split("/")[0],
            "type": document_type,
            "parent_id": document_id,
            "content": text.page_content
        }
        try:
            response = ragdoll.sendChunk(data)
            with (open(f"outputs/output_{i}.log", "a")) as f:
                f.write(f"Response from Ragdoll: {response}\n")
        except Exception as e:
            with (open(f"outputs/error_{i}.log", "a")) as f:
                f.write(f"Error sending to Ragdoll: {e}\n")