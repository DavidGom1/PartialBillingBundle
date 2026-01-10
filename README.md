# Simple Accounting Bundle for Kimai

A Kimai plugin that adds a "Simple Accounting" layer to your projects. It allows you to track "Accumulated" (billable hours), "Invoiced" (exported to Kimai), and "Simple Entries" (manual payments/adjustments) to calculate the **Real Remaining Balance**.

## Features

- **Project Dashboard**: A clean list view of all your accounting projects.
- **Detailed Project View**:
  - **Stats Cards**: Quickly see Accumulated, Invoiced, Simple Payments, and Real Remaining amounts.
  - **Simple Entries**: Add manual positive or negative entries (e.g., partial payments, extra costs, refunds).
- **Multi-language Support**: Available in English and Spanish.
- **Responsive Design**: Works on desktop and mobile.

## Installation

1. Clone this repository into your Kimai plugins directory:
   ```bash
   cd /opt/kimai/var/plugins
   git clone https://github.com/DavidGom1/PartialBillingBundle.git SimpleAccountingBundle
   ```

2. Clear the cache:
   ```bash
   cd /opt/kimai
   bin/console kimai:reload
   ```

3. Run the installation command
   ```bash
   cd /opt/kimai
   bin/console kimai:bundle:simple-accounting:install
   ```

4. The plugin should now be visible in the "System > Plugins" menu and a new "Simple Accounting" menu item will appear.

## Usage

1. Go to **Simple Accounting** in the main menu.
2. Select a project from the list.
3. Use the **New Entry** button to add payments or accounting adjustments.
4. The **Real Remaining** balance will update automatically based on:
   `Remaining = (Billable Work - Invoiced) - Simple Entries`

## Requirements

- Kimai v2.0+
- PHP 7.4+

## License

MIT License
