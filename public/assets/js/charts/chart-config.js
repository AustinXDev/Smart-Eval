function createScoreChart(ctx, labels, data) {
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Average',
        data: data,
        backgroundColor: [
          '#16a34a', 
          '#2563eb', 
          '#facc15', 
          '#f87171', 
          'rgb(130, 0, 0)' 
        ],
        borderWidth: 1
      }]
    },
    options: {   
      responsive: true,
      maintainAspectRatio: false,  
      animation: {
        duration: 1500,
        easing: 'easeOutBounce',
        animateScale: true
      },
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}

function createScorePieChart(ctx, labels, data) {
  return new Chart(ctx, {
    type: 'pie', // ✅ change to pie
    data: {
      labels: labels,
      datasets: [{
        label: 'Average',
        data: data,
        backgroundColor: [
          '#16a34a',  // Excellent
          '#2563eb',  // Good
          '#facc15',  // Fair
          '#f87171',  // Poor
          'rgb(130, 0, 0)' // Very Poor
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        animateRotate: true,
        animateScale: true,  
        duration: 1500,
        easing: 'easeOutBounce'
      },
      plugins: {
        legend: {
          display: true,  
          position: 'bottom'
        }
      }
    }
  });
}

function createScoreDoughnutChart(ctx, labels, data) {
  return new Chart(ctx, {
    type: 'doughnut', // ✅ doughnut chart
    data: {
      labels: labels,
      datasets: [{
        label: 'Score',
        data: data,
        backgroundColor: [
          '#16a34a',  // Excellent
          '#2563eb',  // Good
          '#facc15',  // Fair
          '#f87171',  // Poor
          'rgb(130, 0, 0)' // Very Poor
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        animateRotate: true, 
        animateScale: true,  
        duration: 1500,
        easing: 'easeOutBounce'
      },
      plugins: {
        legend: {
          display: true,
          position: 'bottom'
        },
        tooltip: {
          enabled: true
        }
      },
      cutout: '50%' 
    }
  });
}