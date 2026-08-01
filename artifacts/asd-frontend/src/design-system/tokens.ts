export const colors = {
  primary: {
    50: '#e6f2eb',
    100: '#b3d9c2',
    200: '#80c099',
    300: '#4da670',
    400: '#1a8d47',
    500: '#006233', // Algeria flag green
    600: '#005229',
    700: '#00421f',
    800: '#003215',
    900: '#00220b',
  },
  secondary: {
    50: '#fce8ec',
    100: '#f6b8c4',
    200: '#ef879c',
    300: '#e95674',
    400: '#e2244c',
    500: '#D21034', // Algeria flag red
    600: '#b00e2c',
    700: '#8e0b23',
    800: '#6c081a',
    900: '#4a0511',
  },
  accent: {
    50: '#fdf9ec',
    100: '#f9edc5',
    200: '#f5e19e',
    300: '#f1d477',
    400: '#edc850',
    500: '#C9A227', // Diplomatic gold
    600: '#a88821',
    700: '#876e1a',
    800: '#665414',
    900: '#453a0d',
  },
  neutral: {
    50: '#f8fafc',
    100: '#f1f5f9',
    200: '#e2e8f0',
    300: '#cbd5e1',
    400: '#94a3b8',
    500: '#64748b',
    600: '#475569',
    700: '#334155',
    800: '#1e293b',
    900: '#0f172a',
    950: '#020617',
  },
  success: {
    light: '#dcfce7',
    DEFAULT: '#16a34a',
    dark: '#166534',
  },
  warning: {
    light: '#fef9c3',
    DEFAULT: '#ca8a04',
    dark: '#713f12',
  },
  danger: {
    light: '#fee2e2',
    DEFAULT: '#dc2626',
    dark: '#7f1d1d',
  },
  white: '#ffffff',
  black: '#000000',
} as const

export const typography = {
  fontFamily: {
    sans: '"Inter", "Noto Sans Arabic", system-ui, -apple-system, sans-serif',
    arabic: '"Noto Sans Arabic", "Inter", system-ui, sans-serif',
    mono: '"Fira Code", "Cascadia Code", "Consolas", monospace',
  },
  fontSize: {
    xs: '0.75rem',
    sm: '0.875rem',
    base: '1rem',
    lg: '1.125rem',
    xl: '1.25rem',
    '2xl': '1.5rem',
    '3xl': '1.875rem',
    '4xl': '2.25rem',
    '5xl': '3rem',
  },
  fontWeight: {
    light: '300',
    regular: '400',
    medium: '500',
    semibold: '600',
    bold: '700',
  },
  lineHeight: {
    tight: '1.25',
    snug: '1.375',
    normal: '1.5',
    relaxed: '1.625',
    loose: '2',
  },
} as const

export const spacing = {
  0: '0',
  1: '0.25rem',
  2: '0.5rem',
  3: '0.75rem',
  4: '1rem',
  5: '1.25rem',
  6: '1.5rem',
  8: '2rem',
  10: '2.5rem',
  12: '3rem',
  16: '4rem',
  20: '5rem',
  24: '6rem',
  32: '8rem',
} as const

export const borderRadius = {
  none: '0',
  sm: '0.125rem',
  DEFAULT: '0.375rem',
  md: '0.5rem',
  lg: '0.75rem',
  xl: '1rem',
  '2xl': '1.5rem',
  full: '9999px',
} as const

export const shadows = {
  sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
  DEFAULT: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
  md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
  lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
  xl: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
  none: 'none',
} as const

export const breakpoints = {
  sm: '640px',
  md: '768px',
  lg: '1024px',
  xl: '1280px',
  '2xl': '1536px',
} as const

export const transitions = {
  fast: '150ms ease',
  DEFAULT: '200ms ease',
  slow: '300ms ease',
  slower: '500ms ease',
} as const

export const zIndex = {
  hide: -1,
  base: 0,
  raised: 1,
  dropdown: 1000,
  sticky: 1100,
  overlay: 1200,
  modal: 1300,
  popover: 1400,
  toast: 1500,
  tooltip: 1600,
} as const
