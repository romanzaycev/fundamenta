export const delay = (ms: number): Promise<void> => new Promise((resolve) => setTimeout(resolve, ms));

export const resolve = Promise.resolve;

export const copyToClipboard = (str: string): Promise<void> => navigator.clipboard.writeText(str);

export const getRandomIntInclusive = (min: number, max: number): number => {
    min = Math.ceil(min);
    max = Math.floor(max);

    return Math.floor(Math.random() * (max - min + 1) + min);
}
