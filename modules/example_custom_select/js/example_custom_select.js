window.exampleGetOptions = async function () {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve([
        {
          label: 'Foo',
          value: 'foo',
        },
        {
          label: 'Bar',
          value: 'bar',
        },
        {
          label: 'Wut',
          value: 'wut',
          group: 'Other',
        },
      ]);
    }, 1000); // Simulate a delay of 1 second
  });
};

window.exampleGetOptions1 = function () {
  return [
    {
      label: 'Foo',
      value: 'foo',
      group: 'The Foos',
    },
    {
      label: 'Bar',
      value: 'bar',
      group: 'The Bars',
    },
    {
      label: 'Wut',
      value: 'wut',
      group: 'Other',
    },
  ];
};

window.exampleGetOptions2 = function () {
  return [
    {
      label: 'One',
      value: '1',
    },
    {
      label: 'Two',
      value: '2',
    },
    {
      label: 'Three',
      value: '3',
    },
  ];
};
