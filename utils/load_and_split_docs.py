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
    
    # Load documents
    try:
        docs = load_fn(*load_params)
    except Exception as e:
        with (open("error.log", "a")) as f:
            f.write(f"Error loading documents: {e}\n")
        return

    ragdoll = RagdollService()
    
    # Loop docs
    for i, doc in enumerate(docs):
        try:
            # Split doc into chunks
            chunks = split_fn([doc])
        except Exception as e:
            with (open(".temp/errors/doc_{i}.log", "a")) as f:
                f.write(f"Error splitting document: {e}\n")
            continue
        
        # Loop chunks
        for j, chunk in enumerate(chunks):
            data = {
                "name": chunk.metadata.get("title", "unknown"),
                "source": chunk.metadata.get("source"),
                "type": document_type,
                "parent_id": document_id,
                "content": chunk.page_content,
                "index": j
            }
            # Send to ragdoll service
            try:
                response = ragdoll.sendChunk(data)
                with (open(f".temp/outputs/chunk_{i}_{j}.log", "a")) as f:
                    f.write(f"Response from Ragdoll: {response}\n")
            except Exception as e:
                with (open(f"/temp/errors/error_{i}_{j}.log", "a")) as f:
                    f.write(f"Error sending to Ragdoll: {e}\n")
                continue