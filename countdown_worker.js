// THREAD: this file runs in a separate Web Worker thread, not on the page's main thread.
// It counts down to each meal cut-off every second and sends messages back (message passing, no shared memory).
self.onmessage = (e) => {
  const { serverNow, items } = e.data;     // serverNow = server clock, so a wrong PC clock cannot cheat the cut-off
  const started = Date.now();
  const tick = () => {
    const now = serverNow + (Date.now() - started);
    self.postMessage(items.map(i => ({ id: i.id, ms: i.deadline - now })));
  };
  tick();
  setInterval(tick, 1000);
};
