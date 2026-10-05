# FAQ

[Guide](English.md) · [Deutsch](FAQ-Deutsch.md)

## Data and operation

**Who maintains products and prices?** Shopware only. WordPress stores selections and presentation. No local price calculation.

**What does a 30-minute cache mean?** Data is reused for up to 30 minutes. The next access after expiry fetches new data; no scheduled background fetch. Page/CDN caches can retain rendered prices longer.

**What happens during a shop outage?** Retained content can temporarily appear without expired prices or availability. Without retained data, no product card is shown.

## Bricks

**Do I need Components?** No. Native elements work immediately. Components are optional reusable designs.

**How do I use product search?** Select the slot's native Shopware element in the structure panel.

**Why is a Component empty?** New slots contain no element. Install starter templates or add a native element. Related products require article assignments.

**Do updates overwrite my design?** No. Existing definitions/examples are preserved; extra version copies are optional.

## Installation

**How do I migrate?** Deactivate the old plugin, install and activate the new ZIP, then remove the old plugin. Data is preserved.

**Are GitHub updates available?** After repository publication and a matching release. Until then, update using the local ZIP.

## Shop setup and credentials

**Is the key enough?** No. Configure the shop URL as well. The optional API URL handles a different API base path/host.

**Is .env supported automatically?** No. Hosting must expose DW_SW_ACCESS_KEY to PHP, or wp-config.php must define the constant. A manually included secret file outside the web root is another way to define it.

**Multiple shops at once?** One configured shop per WordPress installation. Different installations can connect different shops.
