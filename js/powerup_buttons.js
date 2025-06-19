// Handles showing/hiding power-up buttons and updating their counts based on inventory
function updatePowerupButtons(inventory) {
    // Count each powerup
    const laserCount = inventory.filter(i => i === 'laserpointer').length;
    const woolCount = inventory.filter(i => i === 'wool').length;
    const milkCount = inventory.filter(i => i === 'milk').length;
    // Show/hide and update count
    document.getElementById('useLaser').style.display = laserCount > 0 ? '' : 'none';
    document.getElementById('useWool').style.display = woolCount > 0 ? '' : 'none';
    document.getElementById('useMilk').style.display = milkCount > 0 ? '' : 'none';
    document.getElementById('laserCount').textContent = laserCount > 0 ? `(${laserCount})` : '';
    document.getElementById('woolCount').textContent = woolCount > 0 ? `(${woolCount})` : '';
    document.getElementById('milkCount').textContent = milkCount > 0 ? `(${milkCount})` : '';
}
