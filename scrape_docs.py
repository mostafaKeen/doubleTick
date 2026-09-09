import os
import sys
import re
import json
import time
import urllib.request
import urllib.parse
import urllib.error
from concurrent.futures import ThreadPoolExecutor, as_completed

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DOCS_DIR = os.path.join(BASE_DIR, "docs")
GUIDES_DIR = os.path.join(DOCS_DIR, "guides")
REFERENCE_DIR = os.path.join(DOCS_DIR, "reference")

os.makedirs(GUIDES_DIR, exist_ok=True)
os.makedirs(REFERENCE_DIR, exist_ok=True)

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
}

def clean_url(url):
    parts = urllib.parse.urlsplit(url)
    encoded_path = urllib.parse.quote(parts.path)
    return urllib.parse.urlunsplit((parts.scheme, parts.netloc, encoded_path, parts.query, parts.fragment))

def fetch_url(raw_url, retries=3):
    url = clean_url(raw_url)
    for attempt in range(retries):
        try:
            req = urllib.request.Request(url, headers=HEADERS)
            with urllib.request.urlopen(req, timeout=20) as resp:
                return resp.read().decode("utf-8", errors="replace")
        except Exception as e:
            if attempt == retries - 1:
                try:
                    print(f"Failed to fetch {raw_url}: {e}".encode('ascii', 'replace').decode('ascii'))
                except Exception:
                    pass
                return None
            time.sleep(1)

def safe_filename(name):
    # Sanitize file name for Windows
    return re.sub(r'[\\/*?:"<>|]', '_', name)

def main():
    print("Fetching llms.txt...")
    llms_txt = fetch_url("https://docs.doubletick.io/llms.txt")
    if not llms_txt:
        print("Error: Could not retrieve llms.txt")
        return

    with open(os.path.join(DOCS_DIR, "llms.txt"), "w", encoding="utf-8") as f:
        f.write(llms_txt)

    # Format: - [Title](URL): Description
    guide_pattern = re.compile(r'-\s+\[(.*?)\]\((https://docs\.doubletick\.io/docs/.*?\.md)\)(?::\s*(.*))?')
    ref_pattern = re.compile(r'-\s+\[(.*?)\]\((https://docs\.doubletick\.io/reference/.*?\.md)\)(?::\s*(.*))?')

    guides = []
    references = []

    current_section = None
    for line in llms_txt.splitlines():
        line_clean = line.strip()
        if line_clean.startswith("## Guides"):
            current_section = "guides"
            continue
        elif line_clean.startswith("## API Reference"):
            current_section = "reference"
            continue

        if current_section == "guides":
            m = guide_pattern.search(line_clean)
            if m:
                guides.append({
                    "title": m.group(1).strip(),
                    "url": m.group(2).strip(),
                    "desc": (m.group(3) or "").strip()
                })
        elif current_section == "reference":
            m = ref_pattern.search(line_clean)
            if m:
                references.append({
                    "title": m.group(1).strip(),
                    "url": m.group(2).strip(),
                    "desc": (m.group(3) or "").strip()
                })

    print(f"Found {len(guides)} Guides and {len(references)} API References.")

    all_tasks = []
    for g in guides:
        raw_fname = g["url"].split("/")[-1]
        fname = safe_filename(urllib.parse.unquote(raw_fname))
        out_path = os.path.join(GUIDES_DIR, fname)
        all_tasks.append(("guide", g, out_path))

    for r in references:
        raw_fname = r["url"].split("/")[-1]
        fname = safe_filename(urllib.parse.unquote(raw_fname))
        out_path = os.path.join(REFERENCE_DIR, fname)
        all_tasks.append(("reference", r, out_path))

    results = []
    print(f"Downloading {len(all_tasks)} files with 8 threads...")

    def download_item(item_type, meta, target_file):
        try:
            # Check if file already exists with non-trivial size
            if os.path.exists(target_file) and os.path.getsize(target_file) > 100:
                with open(target_file, "r", encoding="utf-8") as f:
                    content = f.read()
                return {"status": "ok", "type": item_type, "meta": meta, "path": target_file, "content": content}

            content = fetch_url(meta["url"])
            if content:
                with open(target_file, "w", encoding="utf-8") as f:
                    f.write(content)
                return {"status": "ok", "type": item_type, "meta": meta, "path": target_file, "content": content}
        except Exception as e:
            pass
        return {"status": "failed", "type": item_type, "meta": meta, "path": target_file}

    downloaded = 0
    with ThreadPoolExecutor(max_workers=8) as executor:
        futures = [executor.submit(download_item, t, m, p) for t, m, p in all_tasks]
        for f in as_completed(futures):
            try:
                res = f.result()
                results.append(res)
            except Exception as e:
                pass
            downloaded += 1
            if downloaded % 25 == 0 or downloaded == len(all_tasks):
                print(f"Processed {downloaded}/{len(all_tasks)}...")

    # Now parse all reference OpenAPI definitions
    endpoints = []
    webhooks = []

    for res in results:
        if res.get("status") != "ok":
            continue
        content = res.get("content", "")
        item_type = res["type"]
        meta = res["meta"]

        # Check for OpenAPI block in reference docs
        openapi_matches = re.findall(r'```json\s*(\{.*?"openapi":\s*"3\..*?\})\s*```', content, re.DOTALL)
        if openapi_matches:
            for json_str in openapi_matches:
                try:
                    spec = json.loads(json_str)
                    paths = spec.get("paths", {})
                    for path_val, methods in paths.items():
                        for method_name, method_obj in methods.items():
                            if method_name.lower() in ["get", "post", "put", "patch", "delete"]:
                                endpoints.append({
                                    "title": meta["title"],
                                    "url": meta["url"],
                                    "method": method_name.upper(),
                                    "path": path_val,
                                    "summary": method_obj.get("summary", meta["title"]),
                                    "description": method_obj.get("description", meta["desc"]),
                                    "tags": method_obj.get("tags", []),
                                    "parameters": method_obj.get("parameters", []),
                                    "requestBody": method_obj.get("requestBody", {}),
                                    "responses": list(method_obj.get("responses", {}).keys())
                                })
                except Exception as e:
                    pass

        # Check if it's a webhook doc
        if "webhook" in meta["title"].lower() or "webhook" in meta["url"].lower():
            webhooks.append({
                "title": meta["title"],
                "url": meta["url"],
                "desc": meta["desc"],
                "file": os.path.basename(res["path"])
            })

    catalog = {
        "source": "https://docs.doubletick.io",
        "total_guides": len(guides),
        "total_references": len(references),
        "total_endpoints_parsed": len(endpoints),
        "total_webhooks_parsed": len(webhooks),
        "endpoints": endpoints,
        "webhooks": webhooks,
        "guides": guides
    }

    with open(os.path.join(DOCS_DIR, "doubletick_api_catalog.json"), "w", encoding="utf-8") as f:
        json.dump(catalog, f, indent=2)

    # Also generate a clean markdown summary
    with open(os.path.join(DOCS_DIR, "README_API_CATALOG.md"), "w", encoding="utf-8") as f:
        f.write("# DoubleTick WhatsApp API Complete Scraped Reference & Catalog\n\n")
        f.write(f"- Total Guides: **{len(guides)}**\n")
        f.write(f"- Total Endpoints: **{len(endpoints)}**\n")
        f.write(f"- Total Webhook Specs: **{len(webhooks)}**\n\n")
        f.write("## Parsed Endpoints\n\n")
        f.write("| Method | Endpoint | Title | Description |\n")
        f.write("|---|---|---|---|\n")
        for ep in sorted(endpoints, key=lambda x: (x["path"], x["method"])):
            desc = (ep['description'] or ep['summary'] or '').replace('\n', ' ')[:100]
            f.write(f"| `{ep['method']}` | `{ep['path']}` | [{ep['title']}]({ep['url']}) | {desc} |\n")

        f.write("\n## Webhook Events\n\n")
        for wh in webhooks:
            f.write(f"- **[{wh['title']}]({wh['url']})**: {wh['desc']} (`{wh['file']}`)\n")

    print(f"\nDone! Parsed {len(endpoints)} API endpoints and {len(webhooks)} webhooks.")
    print("Catalog saved to docs/doubletick_api_catalog.json and docs/README_API_CATALOG.md")

if __name__ == "__main__":
    main()
