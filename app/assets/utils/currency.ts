const formatCurrency = (num: number) => {
    // First, format the number as currency
    let formatted = num.toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // Then, split the string into the dollar and cent parts
    const [dollars, cents] = formatted.split('.');

    // Pad the dollar part with leading zeros to make it at least 2 digits long
    const paddedDollars = dollars.replace('$', '').padStart(2, '0');

    // Reassemble and return the padded string
    return `$${paddedDollars}.${cents}`;
}

export {formatCurrency}
