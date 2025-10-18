# Chrome DevTools MCP Server Setup

This MCP server provides Chrome DevTools integration for debugging your WordPress plugin.

## Installation

The package has been installed. VS Code settings are configured in `.vscode/settings.json`.

## Quick Start

1. **Start Chrome with debugging:**
   - Run the VS Code task: `Ctrl+Shift+P` → "Tasks: Run Task" → "Start Chrome with Debugging"
   - Or manually: `chrome.exe --remote-debugging-port=9222 --user-data-dir=.chrome-debug --new-window http://projectstudios.local/wp-admin/admin.php?page=ays-invoicing`

2. **Start the MCP Server:**
   - Run the VS Code task: `Ctrl+Shift+P` → "Tasks: Run Task" → "Start MCP Server"

3. **Debug the AJAX tabs:**
   - Click on different tabs in the invoicing dashboard
   - Use MCP commands to inspect network, console, and DOM

## Configuration

The MCP server accepts these input parameters:
- `browser_url`: URL of the Chrome instance to connect to (default: http://localhost:9222)
- `headless`: Run Chrome in headless mode (true/false)
- `isolated`: Use isolated browser context (true/false)
- `chrome_channel`: Chrome channel to use (stable/beta/dev/canary)

## VS Code Integration

- **Settings**: `.vscode/settings.json` - MCP server configuration
- **Launch**: `.vscode/launch.json` - Chrome debugging configuration
- **Tasks**: `.vscode/tasks.json` - Automated Chrome and MCP startup

## Debugging AJAX Tabs

With this setup, you can:
- Monitor REST API calls to `/wp-json/ays/v1/invoicing/tab/{tab_name}`
- Check if nonces are being sent correctly
- Verify response content loading
- Debug JavaScript errors preventing tab switches

## Manual Commands

If needed, run manually:

```bash
# Start Chrome
chrome.exe --remote-debugging-port=9222 --user-data-dir=.chrome-debug --new-window http://projectstudios.local/wp-admin/admin.php?page=ays-invoicing

# Start MCP Server
cd mcp-server && npm start
```