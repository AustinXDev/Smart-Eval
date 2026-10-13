import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";

const source = await readFile(new URL("../../public/assets/js/services/escape.js", import.meta.url), "utf8");
const { escapeHTML } = await import(`data:text/javascript;base64,${Buffer.from(source).toString("base64")}`);
assert.equal(escapeHTML('<img src=x onerror="alert(1)">'), '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;');
assert.equal(escapeHTML("' & < > "), '&#39; &amp; &lt; &gt; ');
assert.equal(escapeHTML(null), "");
assert.equal(escapeHTML("A & B"), "A &amp; B");
console.log("PASS HTML text and attribute escaping");
